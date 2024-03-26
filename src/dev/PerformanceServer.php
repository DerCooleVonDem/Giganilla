<?php

namespace JonasWindmann\Giganilla\dev;

use pocketmine\scheduler\Task;
use Socket;

class PerformanceServer extends Task {

    public Socket $server;

    public array $labels = [];

    public function __construct()
    {
        $server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        // non blocking
        socket_set_nonblock($server);
        if ($server === false) {
            die("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n");
        }

        $bind = socket_bind($server, 'localhost', 4523);
        if ($bind === false) {
            die("socket_bind() failed: reason: " . socket_strerror(socket_last_error($server)) . "\n");
        }

        socket_listen($server);

        echo "[Giganilla/Dev] Performance server started on localhost:4523\n";

        $this->server = $server;
    }

    public function onRun(): void
    {
        $client = socket_accept($this->server);
        if ($client === false) {
            return;
        }

        $buf = '';
        while (socket_recv($client, $buf, 1024, 0) >= 1) {
            $data = json_decode($buf, true);
            if ($data === null) {
                echo "Received invalid JSON data: $buf\n";
            } else {
                foreach ($data as $label => $time) {

                    if (!isset($this->labels[$label])) {
                        $this->labels[$label] = [];
                    }

                    $this->labels[$label][] = $time;
                }
            }
            $buf = '';
        }

        socket_close($client);
    }

    public function onCancel(): void
    {
        socket_close($this->server);

        echo "[Giganilla/Dev] Performance server stopped\n";

        // print the average of all measurements
        foreach ($this->labels as $label => $times) {
            echo "Average time for $label: " . array_sum($times) / count($times) . "s\n";
        }
    }
}