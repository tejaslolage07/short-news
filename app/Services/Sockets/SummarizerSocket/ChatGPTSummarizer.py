import openai
import os

from dotenv import load_dotenv
load_dotenv()

openai.api_key = os.getenv("OPENAI_API_KEY")

#Just a dummy prompt, the proper one is added in the chatGPT integration PR
def _generate_prompt(body):
    return "Summarize the news article in 60 japanese words:\n" + body


def chatGPTSummarizer(articleBody) -> str:
    return 'Dummy summarized data'


