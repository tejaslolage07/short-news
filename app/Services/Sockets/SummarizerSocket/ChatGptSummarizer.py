import openai
import tiktoken
import os
import json

from dotenv import load_dotenv
load_dotenv()


class ChatGptSummarizer:
    MODEL = "gpt-3.5-turbo"

    def _get_max_tokens(self, num_tokens: int):
        # 300 is set because summary should be short.
        return min(
            300, 2048-num_tokens, num_tokens)

    def _tokenized_prompt(self, text: str):
        encoding = tiktoken.get_encoding("cl100k_base")
        return encoding.encode(text)

    def _format_prompt(self, prompt: str):
        return prompt.strip()

    def _api_call(self, prompt: str, max_tokens: int, temperature: float):
        messages = [{"role": "user", "content": prompt}]
        response = openai.ChatCompletion.create(
            model=self.MODEL,
            messages=messages,
            # lower the temperature to reduce randomness
            temperature=temperature,
            max_tokens=max_tokens,
        )
        return response

    def summarize(self, data: str) -> str:
        try:
            jsonData = json.loads(data)
            chatGptPrompt = jsonData["prompt"]
            maxInputTokens = jsonData["max_input_tokens"]

            openai.api_key = os.getenv("OPENAI_API_KEY")

            textPrompt = self._format_prompt(chatGptPrompt)

            tokenArray = self._tokenized_prompt(
                textPrompt)[0:maxInputTokens]
            num_tokens = len(tokenArray)

            response = self._api_call(
                prompt=textPrompt, max_tokens=self._get_max_tokens(num_tokens), temperature=0.0)

            summarizedText = response["choices"][0].message["content"]
            return summarizedText
        except Exception as e:
            print("Error in summarizer: ", e)
            raise e