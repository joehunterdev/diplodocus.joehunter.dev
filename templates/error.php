<?php

/**
 * Error Template
 *
 * Variables:
 *   $appName  string  — application name
 *   $debug    bool    — show detail block?
 *   $type     string  — exception class name (debug only)
 *   $message  string  — exception message (debug only, already escaped)
 *   $file     string  — file path (debug only, already escaped)
 *   $line     int     — line number (debug only)
 *   $trace    string  — stack trace (debug only, already escaped)
 */

use Diplodocus\TemplateEngine as T;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — <?= T::e($appName) ?></title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, sans-serif;
            background: #0f1117;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .wrap {
            max-width: 680px;
            width: 100%;
        }

        .code {
            font-size: 5rem;
            font-weight: 800;
            color: #e53e3e;
            line-height: 1;
            margin-bottom: .5rem;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: .75rem;
        }

        p {
            color: #94a3b8;
            font-size: .95rem;
            line-height: 1.6;
        }

        a {
            color: #63b3ed;
        }

        .err-detail {
            margin-top: 2rem;
            border-top: 1px solid #2d3748;
            padding-top: 1.5rem;
        }

        .err-type {
            font-size: .8rem;
            font-weight: 700;
            color: #e53e3e;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .35rem;
        }

        .err-msg {
            font-size: 1rem;
            color: #e2e8f0;
            margin-bottom: .75rem;
        }

        .err-location {
            font-size: .8rem;
            color: #718096;
            margin-bottom: 1rem;
        }

        .err-location strong {
            color: #e2e8f0;
        }

        .err-trace {
            background: #1a202c;
            border: 1px solid #2d3748;
            border-radius: 6px;
            padding: 1rem;
            font-size: .75rem;
            line-height: 1.6;
            overflow-x: auto;
            color: #a0aec0;
            white-space: pre-wrap;
            word-break: break-all;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="code">500</div>
        <h1>Something went wrong</h1>
        <p>An error occurred while rendering this page. Please try again or <a href="/">return home</a>.</p>

        <?php if ($debug): ?>
            <div class="err-detail">
                <p class="err-type"><?= T::e($type) ?></p>
                <p class="err-msg"><?= T::e($message) ?></p>
                <p class="err-location"><?= T::e($file) ?> <strong>:<?= (int) $line ?></strong></p>
                <pre class="err-trace"><?= T::e($trace) ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>