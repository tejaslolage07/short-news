<?php

namespace App\Services\Sockets;

// Throws exception if anything goes wrong, so it is the responsibility of the caller to handle the exceptions.
class Summarizer
{
    private $socket;

    public function summarizeOverSocket(string $prompt, int $maxInputTokens): string
    {
        $this->createSocketConnection();
        $this->sendToSocket($prompt, $maxInputTokens);
        $output = $this->readFromSocket();
        $this->closeConnection();
        if ('' === $output) {
            throw new \Exception('No summary returned, check the summarizer logs for errors!');
        }

        return $output;
    }

    private function closeConnection(): void
    {
        socket_close($this->socket);
    }

    private function createSocketConnection(): void
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (false === $this->socket) {
            echo 'Failed to create socket: '.socket_strerror(socket_last_error());

            throw new \Exception('Could not create socket');
        }

        if (false === socket_connect($this->socket, config('app.summarizer_socket_host'), config('app.summarizer_socket_port'))) {
            echo 'Failed to connect to socket: '.socket_strerror(socket_last_error());

            throw new \Exception('Could not connect to socket');
        }
    }

    private function readFromSocket(): string
    {
        // The summary will fit within 4096 bytes (since it is max 300 tokens)
        $out = socket_read($this->socket, 4096);
        if (false === $out) {
            echo 'Failed to read from socket: '.socket_strerror(socket_last_error());

            throw new \Exception('Could not read from socket');
        }

        return $out;
    }

    private function formatData(string $prompt, int $maxInputTokens)
    {
        return json_encode([
            'prompt' => $prompt,
            'max_input_tokens' => $maxInputTokens,
        ]);
    }

    private function sendToSocket(string $prompt, int $maxInputTokens)
    {
        $data = $this->formatData($prompt, $maxInputTokens);
        if (false === socket_write($this->socket, $data)) {
            echo 'Failed to write to socket: '.socket_strerror(socket_last_error());

            throw new \Exception('Could not write to socket');
        }
    }
}
