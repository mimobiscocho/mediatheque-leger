<?php
/**
 * Journalisation applicative.
 * Enregistre les événements métier et de sécurité dans un fichier rotatif mensuel.
 * Format : [DATE] [NIVEAU] [IP] [AGENT] Message
 */
class Logger
{
    private static string $logDir = '';

    private static function init(): void
    {
        if (self::$logDir === '') {
            self::$logDir = ROOT . '/logs';
            if (!is_dir(self::$logDir)) {
                @mkdir(self::$logDir, 0750, true);
            }
        }
    }

    private static function write(string $level, string $message): void
    {
        self::init();
        $file = self::$logDir . '/app_' . date('Y-m') . '.log';

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $agent = 'anonyme';
        if (!empty($_SESSION['agent'])) {
            $agent = $_SESSION['agent']['email'] . ' (' . $_SESSION['agent']['role'] . ')';
        }

        $line = sprintf(
            "[%s] [%s] [%s] [%s] %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $ip,
            $agent,
            $message
        );

        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    public static function warning(string $message): void
    {
        self::write('WARNING', $message);
    }

    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }

    public static function security(string $message): void
    {
        self::write('SECURITY', $message);
    }
}
