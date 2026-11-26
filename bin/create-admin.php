<?php

/**
 * Create an admin user.
 *
 * Usage:
 *   php bin/create-admin.php [--email=you@example.com] [--name="Your Name"]
 *
 * Email and name are prompted for if not given. The password is always prompted for
 * (hidden, entered twice) and is never accepted as a flag, so it stays out of shell
 * history and process listings.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

require dirname(__DIR__) . '/src/bootstrap.php';

const MIN_PASSWORD_LENGTH = 12;
const MAX_PASSWORD_BYTES  = 72; // bcrypt (PASSWORD_DEFAULT) ignores bytes beyond 72

function fail(string $message): void
{
    fwrite(STDERR, "Error: {$message}\n");
    exit(1);
}

function readLineFromStdin(): string
{
    $line = fgets(STDIN);
    if ($line === false) {
        fail('No input received.');
    }

    return rtrim($line, "\r\n");
}

function prompt(string $label): string
{
    fwrite(STDOUT, $label);

    return trim(readLineFromStdin());
}

function promptHidden(string $label): string
{
    fwrite(STDOUT, $label);

    $isTty = stream_isatty(STDIN);
    if ($isTty) {
        shell_exec('stty -echo');
    }
    try {
        $value = readLineFromStdin();
    } finally {
        if ($isTty) {
            shell_exec('stty echo');
            fwrite(STDOUT, PHP_EOL);
        }
    }

    return $value;
}

function validateEmail(string $email): string
{
    $email = strtolower(trim($email));
    if (strlen($email) > 191 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        fail('Please provide a valid email address (max 191 characters).');
    }

    return $email;
}

function validateName(string $name): string
{
    $name = trim($name);
    if (preg_match('/^.{1,100}$/u', $name) !== 1 || preg_match('/\p{Cc}/u', $name) === 1) {
        fail('Name must be 1-100 characters with no control characters.');
    }

    return $name;
}

$options = getopt('h', ['email:', 'name:', 'help']);
if ($options === false || isset($options['h']) || isset($options['help'])) {
    fwrite(STDOUT, "Usage: php bin/create-admin.php [--email=EMAIL] [--name=NAME]\n");
    exit(0);
}

$emailArg = $options['email'] ?? null;
$nameArg  = $options['name'] ?? null;
if (is_array($emailArg) || is_array($nameArg)) {
    fail('Pass --email and --name at most once each.');
}

$email = validateEmail($emailArg ?? prompt('Email: '));
$name  = validateName($nameArg ?? prompt('Name: '));

$password = promptHidden('Password (min ' . MIN_PASSWORD_LENGTH . ' characters): ');
if (strlen($password) < MIN_PASSWORD_LENGTH) {
    fail('Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.');
}
if (strlen($password) > MAX_PASSWORD_BYTES) {
    fail('Password must be at most ' . MAX_PASSWORD_BYTES . ' bytes.');
}
if (!hash_equals($password, promptHidden('Confirm password: '))) {
    fail('Passwords do not match.');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
unset($password);

try {
    $pdo  = App\Database\Connection::fromEnv();
    $stmt = $pdo->prepare(
        'INSERT INTO users (email, password_hash, name, role, is_active)
         VALUES (:email, :password_hash, :name, :role, 1)'
    );
    $stmt->execute([
        'email'         => $email,
        'password_hash' => $hash,
        'name'          => $name,
        'role'          => 'admin',
    ]);
} catch (RuntimeException $e) {
    // Missing configuration (from Env::require); message names the key only.
    fail($e->getMessage() . ' (check .env)');
} catch (PDOException $e) {
    // Never print the raw message: it can include the DB user and host.
    $driverCode = $e->errorInfo[1] ?? null;
    if ($driverCode === 1062) {
        fail("A user with email {$email} already exists.");
    }
    $hint = match ($driverCode ?? (int) $e->getCode()) {
        1045    => 'access denied: check DB_USERNAME / DB_PASSWORD in .env',
        1049    => 'unknown database: check DB_NAME in .env',
        1146    => 'users table missing: run the migrations first (see INSTALL.md)',
        2002    => 'cannot reach MySQL: is it running on DB_HOST / DB_PORT?',
        default => 'error code ' . ($driverCode ?? $e->getCode()),
    };
    fail("Database error ({$hint}).");
}

fwrite(STDOUT, "Created admin user #{$pdo->lastInsertId()} ({$email}).\n");
