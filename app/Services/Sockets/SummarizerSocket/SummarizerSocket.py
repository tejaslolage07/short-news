import socket

from ChatGptSummarizer import ChatGptSummarizer

server = None
client = None
BUFFER_SIZE = 1024


def main():
    try:
        createSocket()
    except Exception as e:
        print(f"Error in opening socket: ${e}")
        raise e

    try:
        listenForConnections()
    except Exception as e:
        print(f"Error in listening for connections: ${e}")
    finally:
        server.close()


def createSocket():
    global server
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    port = 8100
    ip = 'localhost'
    server.bind((ip, port))
    server.listen(5)
    print(f"Server is listening on {ip}:{port}")


def listenForConnections():
    global server
    global client
    print("Started listening for connections")
    while True:
        client, addr = server.accept()
        print(f"Connection from {addr} has been established")

        handleClient()
        client.close()

        print("Connection closed")


def handleClient():
    try:
        data = recvData().decode('utf-8')
        summarizedData = bytes(ChatGptSummarizer().summarize(data), "utf-8")
    except Exception as e:
        summarizedData = b''
        print(f"Error in handling client request to summarize: ${e}")
    finally:
        sendData(summarizedData)


def recvData() -> bytes:
    data = b''
    global client
    while True:
        packet = client.recv(BUFFER_SIZE)
        data += packet
        if len(packet) < BUFFER_SIZE:
            break
    return data


def sendData(data):
    global client
    client.sendall(data)


if __name__ == "__main__":
    main()
