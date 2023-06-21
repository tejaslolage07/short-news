<?php

namespace App\Services\Sockets;


#Throws exception if anything goes wrong, and it is the responsibility of the caller to handle the exceptions.
#If all goes well, then the summary is returned as a string.
class Summarizer
{
    private $socket;

    /**
     * @param string $data
     * @param string $prompt
     * @return string
     * @throws \Exception
     */
    public function summarizeOverSocket(string $prompt, int $maxInputTokens): string
    {
        
        #create connection
        $this->createSocketConnection();
        #send data and prompt over the socket
        $this->sendToSocket($prompt, $maxInputTokens);
        #read the response
        $output = $this->readFromSocket();
        #close the connection
        $this->closeConnection();
        #throw exception if no summary is returned
        if($output === '') {
            throw new \Exception('No summary returned, check the summarizer logs for errors!');
        }
        return $output;
    }

    private function closeConnection(): void {
        socket_close($this->socket);
    }

    private function createSocketConnection(): void
    {
        # Create a TCP socket
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            print("Failed to create socket: " . socket_strerror(socket_last_error()));
            throw new \Exception('Could not create socket');
        }

        # Connect the socket to the address:port
        if(false === socket_connect($this->socket, config('app.summarizer_socket_host'), config('app.summarizer_socket_port'))) {
            print("Failed to connect to socket: " . socket_strerror(socket_last_error()));
            throw new \Exception('Could not connect to socket');
        }
    }

    private function readFromSocket(): string
    {
        #The summary will fit within 4096 bytes
        $out = socket_read($this->socket, 4096);
        if($out === false) {
            print("Failed to read from socket: " . socket_strerror(socket_last_error()));
            throw new \Exception('Could not read from socket');
        }
        return $out;
    }

    private function formatData(string $prompt, int $maxInputTokens){
        return json_encode([
            'prompt' => $prompt,
            'max_input_tokens' => $maxInputTokens
        ]);
    }

    private function sendToSocket(string $prompt, int $maxInputTokens)
    {
        $data = $this->formatData($prompt, $maxInputTokens);
        if (false === socket_write($this->socket, $data)) {
            print("Failed to write to socket: " . socket_strerror(socket_last_error()));
            throw new \Exception('Could not write to socket');
        }
    }

}