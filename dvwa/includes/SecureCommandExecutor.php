<?php
class SecureCommandExecutor {
    private $allowedCommands = array(
        'ping' => array(
            'windows' => 'C:\\Windows\\System32\\ping.exe',
            'unix' => '/bin/ping'
        )
    );
    private $timeout = 30;
    private $maxOutputSize = 1048576;
    public function executePing($target) {
        $validatedIp = $this->validateIpAddress($target);
        if ($validatedIp === false) {
            return array('success' => false, 'output' => '', 'error' => 'Invalid IP address format.');
        }
        $isWindows = stristr(php_uname('s'), 'Windows NT') !== false;
        $pingCommand = $this->getPingCommand($isWindows);
        if ($pingCommand === false) {
            return array('success' => false, 'output' => '', 'error' => 'Ping command not available.');
        }
        $args = $this->buildPingArguments($isWindows, $validatedIp);
        return $this->executeCommand($pingCommand, $args);
    }
    private function validateIpAddress($ip) {
        $ip = trim($ip);
        $validated = filter_var($ip, FILTER_VALIDATE_IP);
        if ($validated === false) {
            return false;
        }
        return $validated;
    }
    private function getPingCommand($isWindows) {
        if ($isWindows) {
            $command = $this->allowedCommands['ping']['windows'];
            if (!file_exists($command)) {
                $command = 'ping';
            }
        } else {
            $command = $this->allowedCommands['ping']['unix'];
            if (!file_exists($command)) {
                $alternatives = array('/usr/bin/ping', '/sbin/ping', '/usr/sbin/ping');
                foreach ($alternatives as $alt) {
                    if (file_exists($alt)) {
                        $command = $alt;
                        break;
                    }
                }
            }
        }
        return $command;
    }
    private function buildPingArguments($isWindows, $ip) {
        $args = array();
        if ($isWindows) {
            $args[] = '-n';
            $args[] = '4';
        } else {
            $args[] = '-c';
            $args[] = '4';
        }
        $args[] = escapeshellarg($ip);
        return $args;
    }
    private function executeCommand($command, $args) {
        $fullCommand = $command;
        foreach ($args as $arg) {
            $fullCommand .= ' ' . $arg;
        }
        $output = array();
        $returnCode = 0;
        $oldTimeLimit = ini_get('max_execution_time');
        set_time_limit($this->timeout);
        exec($fullCommand . ' 2>&1', $output, $returnCode);
        set_time_limit($oldTimeLimit);
        $outputString = implode("\n", $output);
        if (strlen($outputString) > $this->maxOutputSize) {
            $outputString = substr($outputString, 0, $this->maxOutputSize) . "\n[Output truncated]";
        }
        $outputString = htmlspecialchars($outputString, ENT_QUOTES, 'UTF-8');
        return array('success' => ($returnCode === 0), 'output' => $outputString, 'error' => ($returnCode !== 0) ? "Command failed" : '');
    }
}
?>
