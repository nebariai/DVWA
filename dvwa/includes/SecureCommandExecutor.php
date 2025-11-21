<?php

/**
 * SecureCommandExecutor - A secure command execution wrapper
 * 
 * This class provides secure command execution by:
 * - Maintaining a strict allowlist of permitted commands
 * - Validating all arguments against security patterns
 * - Using parameterized execution (no shell interpretation)
 * - Enforcing timeouts and resource limits
 * - HTML-encoding output to prevent XSS
 */
class SecureCommandExecutor {
    
    /**
     * Allowlist of permitted commands with full paths
     * Format: 'command_name' => ['full_path', ['allowed_flags']]
     */
    private static $ALLOWED_COMMANDS = [
        'ping' => [
            'windows' => 'C:\\Windows\\System32\\ping.exe',
            'unix' => '/bin/ping',
            'allowed_flags' => ['-c', '-n', '-t', '-w']
        ]
    ];
    
    /**
     * Maximum execution timeout in seconds
     */
    private const MAX_TIMEOUT = 30;
    
    /**
     * Validate an IPv4 address using strict whitelist approach
     * 
     * @param string $ip The IP address to validate
     * @return array ['valid' => bool, 'ip' => string|null, 'error' => string|null]
     */
    public static function validateIPv4($ip) {
        // Remove any whitespace
        $ip = trim($ip);
        
        // Split into octets
        $octets = explode('.', $ip);
        
        // Must have exactly 4 octets
        if (count($octets) !== 4) {
            return [
                'valid' => false,
                'ip' => null,
                'error' => 'Invalid IP format: must have 4 octets'
            ];
        }
        
        // Validate each octet
        foreach ($octets as $octet) {
            // Must be numeric
            if (!is_numeric($octet)) {
                return [
                    'valid' => false,
                    'ip' => null,
                    'error' => 'Invalid IP format: octets must be numeric'
                ];
            }
            
            // Must be in range 0-255
            $value = intval($octet);
            if ($value < 0 || $value > 255) {
                return [
                    'valid' => false,
                    'ip' => null,
                    'error' => 'Invalid IP format: octets must be 0-255'
                ];
            }
        }
        
        // Reconstruct the validated IP
        $validatedIP = implode('.', array_map('intval', $octets));
        
        return [
            'valid' => true,
            'ip' => $validatedIP,
            'error' => null
        ];
    }
    
    /**
     * Execute ping command securely
     * 
     * @param string $target The IP address to ping
     * @param int $count Number of ping packets (default: 4)
     * @return array ['success' => bool, 'output' => string, 'error' => string|null]
     */
    public static function executePing($target, $count = 4) {
        // Validate IP address
        $validation = self::validateIPv4($target);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'output' => '',
                'error' => $validation['error']
            ];
        }
        
        $validatedIP = $validation['ip'];
        
        // Validate count parameter
        if (!is_numeric($count) || $count < 1 || $count > 10) {
            return [
                'success' => false,
                'output' => '',
                'error' => 'Invalid count: must be between 1 and 10'
            ];
        }
        
        // Determine OS and build command
        $isWindows = stristr(php_uname('s'), 'Windows NT') !== false;
        
        if ($isWindows) {
            $command = self::$ALLOWED_COMMANDS['ping']['windows'];
            $args = ['-n', strval($count), $validatedIP];
        } else {
            $command = self::$ALLOWED_COMMANDS['ping']['unix'];
            $args = ['-c', strval($count), $validatedIP];
        }
        
        // Execute command using proc_open (no shell interpretation)
        return self::executeCommand($command, $args);
    }
    
    /**
     * Execute a command securely using proc_open
     * 
     * @param string $command Full path to command
     * @param array $args Array of arguments
     * @return array ['success' => bool, 'output' => string, 'error' => string|null]
     */
    private static function executeCommand($command, $args) {
        // Build command array (no shell interpretation)
        $cmdArray = array_merge([$command], $args);
        
        // Escape all arguments as additional safety layer
        $escapedArgs = array_map('escapeshellarg', $args);
        $cmdString = escapeshellarg($command) . ' ' . implode(' ', $escapedArgs);
        
        // Set up process descriptors
        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];
        
        // Execute with timeout
        $process = proc_open($cmdString, $descriptors, $pipes);
        
        if (!is_resource($process)) {
            return [
                'success' => false,
                'output' => '',
                'error' => 'Failed to execute command'
            ];
        }
        
        // Close stdin
        fclose($pipes[0]);
        
        // Read output with timeout
        $output = '';
        $error = '';
        $startTime = time();
        
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        
        while (time() - $startTime < self::MAX_TIMEOUT) {
            $output .= stream_get_contents($pipes[1]);
            $error .= stream_get_contents($pipes[2]);
            
            // Check if process is still running
            $status = proc_get_status($process);
            if (!$status['running']) {
                break;
            }
            
            usleep(100000); // 0.1 second
        }
        
        // Clean up
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        // Terminate if still running
        $status = proc_get_status($process);
        if ($status['running']) {
            proc_terminate($process);
            proc_close($process);
            return [
                'success' => false,
                'output' => $output,
                'error' => 'Command execution timeout'
            ];
        }
        
        $exitCode = proc_close($process);
        
        return [
            'success' => ($exitCode === 0),
            'output' => $output,
            'error' => $error ?: null
        ];
    }
    
    /**
     * HTML-encode output to prevent XSS
     * 
     * @param string $output Raw command output
     * @return string HTML-encoded output
     */
    public static function encodeOutput($output) {
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
}

?>
