FROM python:latest

WORKDIR /socket

COPY ./requirements.txt .

RUN pip install -r requirements.txt

COPY ./app/Services/Sockets/SummarizerSocket .

EXPOSE 8100

CMD ["python3", "SummarizerSocket.py"]