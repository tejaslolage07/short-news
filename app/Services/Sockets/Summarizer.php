<?php

namespace App\Services\Sockets;

class Summarizer
{
    private $socket;

    public function summarizeOverSocket(string $prompt, int $maxInputTokens): string
    {
        $this->createSocket();
        $this->connectToSocket();
        $this->formatAndSendToSocket($prompt, $maxInputTokens);
        $output = $this->readFromSocket();
        $this->closeConnection();
        if ('' === $output || null === $output) {
            throw new \Exception('No summary returned, check the summarizer logs for errors!');
        }

        return $output;
    }

    private function createSocket(): void
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (false === $this->socket) {
            throw new \Exception('Failed to create socket: '.socket_strerror(socket_last_error()));
        }
    }

    private function connectToSocket(): void
    {
        $host = config('app.summarizer_socket_host');
        $port = config('app.summarizer_socket_port');

        $status = socket_connect($this->socket, $host, $port);
        if (false === $status) {
            throw new \Exception('Failed to connect to socket: '.socket_strerror(socket_last_error()));
        }
    }

    private function formatAndSendToSocket(string $prompt, int $maxInputTokens): void
    {
        $data = $this->formatData($prompt, $maxInputTokens);
        $status = socket_write($this->socket, $data);
        if (false === $status) {
            throw new \Exception('Failed to write to socket: '.socket_strerror(socket_last_error()));
        }
    }

    private function formatData(string $prompt, int $maxInputTokens): string
    {
        return json_encode([
            'prompt' => $prompt,
            'max_input_tokens' => $maxInputTokens,
        ]);
    }

    private function readFromSocket(): string
    {
        // The summary will fit within 4096 bytes (since it is max 300 tokens)
        $out = socket_read($this->socket, 4096);
        if (false === $out) {
            throw new \Exception('Failed to read from socket: '.socket_strerror(socket_last_error()));
        }

        return $out;
    }

    private function closeConnection(): void
    {
        socket_close($this->socket);
    }
}
