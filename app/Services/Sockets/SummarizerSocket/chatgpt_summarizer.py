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
            json_data = json.loads(data)
            chat_gpt_prompt = json_data["prompt"]
            max_input_tokens = json_data["max_input_tokens"]

            openai.api_key = os.getenv("OPENAI_API_KEY")

            text_prompt = self._format_prompt(chat_gpt_prompt)

            token_array = self._tokenized_prompt(
                text_prompt)[0:max_input_tokens]
            num_tokens = len(token_array)

            response = self._api_call(
                prompt=text_prompt, max_tokens=self._get_max_tokens(num_tokens), temperature=0.0)

            summarized_text = response["choices"][0].message["content"]
            return summarized_text
        except Exception as _e:
            print("Error in summarizer: ", _e)
            raise _e
