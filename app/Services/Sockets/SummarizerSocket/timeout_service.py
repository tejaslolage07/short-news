import signal

class TimeOutService:
    def __init__(self, timeout, error_msg_on_timeout="Timed out!"):
        self.timeout = timeout
        self.error_msg = error_msg_on_timeout


    def handle_timeout(self, signum, frame):
        raise TimeoutError(self.error_msg)

    def set_timeout(self):
        signal.signal(signal.SIGALRM, self.handle_timeout)
        signal.alarm(self.timeout)

    def clear_timeout(self):
        signal.alarm(0)