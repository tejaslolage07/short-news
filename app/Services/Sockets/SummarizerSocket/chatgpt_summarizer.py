import openai
import os
import json

from tokenizer_service import TokenizerService

from dotenv import load_dotenv
load_dotenv()


class ChatGptSummarizer:
    MODEL = "gpt-3.5-turbo"

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

            text_prompt = TokenizerService.limit_tokens(
                text_prompt, self.calculate_max_input_tokens(max_input_tokens))

            response = self._api_call(
                prompt=text_prompt, max_tokens=300, temperature=0.0)

            summarized_text = response["choices"][0].message["content"]
            return summarized_text
        except Exception as _e:
            print("Error in summarizer: ", _e)
            raise _e

    def calculate_max_input_tokens(self, num_tokens: int) -> int:
        # 4096 is the context length, it takes 4 token per request.
        # 300 is the desired num of tokens for output.
        # so the max is whatever is smaller of 4096 - 4 - 300 and user passed limit.
        return min(4096 - 4 - 300, num_tokens)
