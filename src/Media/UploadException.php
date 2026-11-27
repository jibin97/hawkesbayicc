<?php

declare(strict_types=1);

namespace App\Media;

use RuntimeException;

/** An image could not be accepted; the message is written for the admin and safe to display. */
final class UploadException extends RuntimeException
{
}
