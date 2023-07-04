import socket

from chatgpt_summarizer import ChatGptSummarizer
from timeout_service import TimeOutService

SERVER = None
CLIENT = None
BUFFER_SIZE = 1024
TIMEOUT_DURATION = 30


def main():
    try:
        create_socket()
    except Exception as _e:
        print(f"Error in opening socket: ${_e}")
        raise _e

    try:
        listen_for_connections()
    except Exception as _e:
        print(f"Error in listening for connections: ${_e}")
    finally:
        SERVER.close()


def create_socket():
    global SERVER
    SERVER = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    port = 8100
    ip = '0.0.0.0'
    SERVER.bind((ip, port))
    SERVER.listen(5)
    print(f"SERVER is listening on {ip}:{port}")


def listen_for_connections():
    global SERVER
    global CLIENT
    print("Started listening for connections")
    while True:
        CLIENT, addr = SERVER.accept()
        print(f"Connection from {addr} has been established")

        handle_client()
        CLIENT.close()

        print("Connection closed")


def handle_client():
    timeout_service = TimeOutService(TIMEOUT_DURATION, "Timed out while trying to summarize!")
    summarized_data = b''
    try:
        timeout_service.set_timeout()
        data = recv_data().decode('utf-8')
        summarized_data = bytes(ChatGptSummarizer().summarize(data), "utf-8")
        timeout_service.clear_timeout()
    except TimeoutError as _e:
        pass
    except Exception as _e:
        print(f"Error in handling CLIENT request to summarize: ${_e}")
    finally:
        send_data(summarized_data)


def recv_data() -> bytes:
    data = b''
    global CLIENT
    while True:
        packet = CLIENT.recv(BUFFER_SIZE)
        data += packet
        if len(packet) < BUFFER_SIZE:
            break
    return data


def send_data(data):
    global CLIENT
    CLIENT.sendall(data)


if __name__ == "__main__":
    main()
