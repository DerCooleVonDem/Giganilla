<?php

namespace JonasWindmann\Giganilla\dev;

use pocketmine\utils\SingletonTrait;

class PerformanceUtility
{
    use SingletonTrait;

    private array $measurements = [];

    public function startMeasurement(string $label): void
    {
        $this->measurements[$label] = ['start' => microtime(true)];
    }

    public function stopMeasurement(string $label): void
    {
        if (isset($this->measurements[$label])) {
            $this->measurements[$label]['end'] = microtime(true);
        }
    }

    public function getExecutionTime(string $label): float
    {
        if (isset($this->measurements[$label]['end'])) {
            return $this->measurements[$label]['end'] - $this->measurements[$label]['start'];
        }
        return 0;
    }

    public function getAllMeasurements(): array
    {
        $results = [];
        foreach ($this->measurements as $label => $measurement) {
            if (isset($measurement['end'])) {
                $results[$label] = $measurement['end'] - $measurement['start'];
            }
        }
        return $results;
    }

    public function sendMeasurementsToSocket(): void
    {

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            die("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
        }

        connection:
        try {
            $result = socket_connect($socket, 'localhost', 4523);
        } catch (\Throwable $e) {
            goto connection;
        }
        if ($result === false) {
            goto connection;
        }

        // check if the socket ready to send data
        $read = [$socket];
        $write = $except = null;
        $result = socket_select($read, $write, $except, 0);
        if ($result === false) {
            echo "socket_select() failed: reason: " . socket_strerror(socket_last_error($socket)) . "\n";
            goto connection;
        }

        $measurements = $this->getAllMeasurements();

        // remove all the measurements so we don't send the same data again
        $this->measurements = [];

        $data = json_encode($measurements);
        $bytesSent = socket_send($socket, $data, strlen($data), 0);
        if ($bytesSent === false) {
            die("socket_send() failed: reason: " . socket_strerror(socket_last_error($socket)) . "\n");
        }

        socket_close($socket);
    }
}
