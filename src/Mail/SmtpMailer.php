<?php

declare(strict_types=1);

namespace App\Mail;

use App\Config\Env;
use RuntimeException;

/**
 * Minimal SMTP client (plain-text mail, AUTH LOGIN, STARTTLS or implicit TLS). No dependencies.
 *
 * .env keys:
 *   MAIL_HOST, MAIL_PORT (587), MAIL_ENCRYPTION (tls = STARTTLS | ssl = implicit TLS | none),
 *   MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS, MAIL_FROM_NAME, MAIL_NOTIFY_TO (comma-separated)
 */
final class SmtpMailer
{
    private const TIMEOUT = 15;

    /** @var resource|null */
    private $socket = null;

    private function __construct(
        private string $host,
        private int $port,
        private string $encryption,
        private string $username,
        private string $password,
        private string $fromAddress,
        private string $fromName,
    ) {
    }

    /** Null when mail isn't configured (MAIL_HOST empty): callers should skip sending. */
    public static function fromEnv(): ?self
    {
        $host = trim((string) Env::get('MAIL_HOST', ''));
        if ($host === '') {
            return null;
        }
        $encryption = strtolower((string) Env::get('MAIL_ENCRYPTION', 'tls'));
        if (!in_array($encryption, ['tls', 'ssl', 'none'], true)) {
            throw new RuntimeException('MAIL_ENCRYPTION must be tls, ssl or none.');
        }

        return new self(
            $host,
            (int) Env::get('MAIL_PORT', $encryption === 'ssl' ? '465' : '587'),
            $encryption,
            (string) Env::get('MAIL_USERNAME', ''),
            (string) Env::get('MAIL_PASSWORD', ''),
            Env::require('MAIL_FROM_ADDRESS'),
            (string) Env::get('MAIL_FROM_NAME', 'Website'),
        );
    }

    /**
     * @param list<string> $to
     * @throws RuntimeException on any SMTP failure (message never includes credentials)
     */
    public function send(array $to, string $subject, string $body, ?string $replyTo = null): void
    {
        $to = array_values(array_filter($to, static fn (string $a): bool => filter_var($a, FILTER_VALIDATE_EMAIL) !== false));
        if ($to === []) {
            throw new RuntimeException('No valid recipient address.');
        }
        if ($replyTo !== null && filter_var($replyTo, FILTER_VALIDATE_EMAIL) === false) {
            $replyTo = null;
        }

        try {
            $this->connect();
            $this->ehlo();

            if ($this->encryption === 'tls') {
                $this->command('STARTTLS', 220);
                if (stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT) !== true) {
                    throw new RuntimeException('STARTTLS negotiation failed.');
                }
                $this->ehlo();
            }

            if ($this->username !== '') {
                $this->command('AUTH LOGIN', 334);
                $this->command(base64_encode($this->username), 334, 'AUTH username');
                $this->command(base64_encode($this->password), 235, 'AUTH password');
            }

            $this->command('MAIL FROM:<' . $this->fromAddress . '>', 250);
            foreach ($to as $rcpt) {
                $this->command('RCPT TO:<' . $rcpt . '>', [250, 251]);
            }
            $this->command('DATA', 354);
            $this->write($this->buildMessage($to, $subject, $body, $replyTo) . "\r\n.\r\n");
            $this->expect(250, 'message body');
            $this->command('QUIT', 221);
        } finally {
            if (is_resource($this->socket)) {
                fclose($this->socket);
            }
            $this->socket = null;
        }
    }

    /** @param list<string> $to */
    private function buildMessage(array $to, string $subject, string $body, ?string $replyTo): string
    {
        $domain  = substr(strrchr($this->fromAddress, '@') ?: '@localhost', 1);
        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . self::encodeHeader($this->fromName) . ' <' . $this->fromAddress . '>',
            'To: ' . implode(', ', $to),
            'Subject: ' . self::encodeHeader($subject),
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . $domain . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            'Auto-Submitted: auto-generated',
        ];
        if ($replyTo !== null) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        // base64 body: no dot-stuffing or line-length issues, safe for any UTF-8 text.
        $normalised = str_replace(["\r\n", "\r"], "\n", $body);
        $encoded    = rtrim(chunk_split(base64_encode(str_replace("\n", "\r\n", $normalised)), 76, "\r\n"));

        return implode("\r\n", $headers) . "\r\n\r\n" . $encoded;
    }

    /** RFC 2047 encoding; also strips CR/LF so values can never inject headers. */
    private static function encodeHeader(string $value): string
    {
        $value = str_replace(["\r", "\n"], ' ', $value);

        return preg_match('/^[\x20-\x7E]*$/', $value) === 1
            ? $value
            : '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function connect(): void
    {
        $remote = ($this->encryption === 'ssl' ? 'ssl://' : 'tcp://') . $this->host . ':' . $this->port;
        $context = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $this->host]]);
        $socket  = @stream_socket_client($remote, $errno, $errstr, self::TIMEOUT, STREAM_CLIENT_CONNECT, $context);
        if ($socket === false) {
            throw new RuntimeException("Could not connect to mail server ({$errno}).");
        }
        stream_set_timeout($socket, self::TIMEOUT);
        $this->socket = $socket;
        $this->expect(220, 'greeting');
    }

    private function ehlo(): void
    {
        $this->command('EHLO ' . (gethostname() ?: 'localhost'), 250);
    }

    /** @param int|list<int> $expected */
    private function command(string $line, int|array $expected, ?string $label = null): void
    {
        $this->write($line . "\r\n");
        // Label hides AUTH payloads from error messages.
        $this->expect($expected, $label ?? strtok($line, ' :'));
    }

    private function write(string $data): void
    {
        if (fwrite($this->socket, $data) === false) {
            throw new RuntimeException('Lost connection to mail server.');
        }
    }

    /** @param int|list<int> $expected */
    private function expect(int|array $expected, string $step): void
    {
        $expected = (array) $expected;
        $response = '';
        while (($line = fgets($this->socket, 1024)) !== false) {
            $response .= $line;
            // Multi-line replies use "250-"; the last line uses "250 ".
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expected, true)) {
            $meta = stream_get_meta_data($this->socket);
            $why  = $response === '' ? ($meta['timed_out'] ? 'timed out' : 'no response') : 'got ' . $code;
            throw new RuntimeException("SMTP error at {$step}: {$why}.");
        }
    }
}
