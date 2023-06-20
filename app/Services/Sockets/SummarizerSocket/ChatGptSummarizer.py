import openai
import tiktoken
import os

from dotenv import load_dotenv
load_dotenv()

class ChatGptSummarizer:
    MAX_INPUT_TOKENS = None
    MODEL=None

    def _tokenized_prompt(self, text):
        #get the number of tokens in the article body
        encoding = tiktoken.get_encoding("cl100k_base")
        return encoding.encode(text)

    def _format_prompt(self, prompt:str):
        #Strip the prompt of whitespaces.
        return prompt.strip()


    def _api_call(self, prompt, max_tokens:int, temperature:float):
        messages = [{"role": "user" , "content": prompt}]
        response = openai.ChatCompletion.create(
            model=self.MODEL,
            messages=messages,
            #lower the temperature to reduce randomness
            temperature=temperature,
            max_tokens=max_tokens,
        )
        return response

    def summarize(self, chatGptPrompt:str) -> str:
        try:
            #get the environment variables
            openai.api_key = os.getenv("OPENAI_API_KEY")
            self.MODEL=os.getenv("GPT_MODEL")
            self.MAX_INPUT_TOKENS = int(os.getenv("MAX_INPUT_TOKENS"))

            textPrompt = self._format_prompt(chatGptPrompt)
            # textPrompt = self._generate_prompt(articleBody)
            print(textPrompt)

            tokenArray = self._tokenized_prompt(textPrompt)[0:self.MAX_INPUT_TOKENS]
            num_tokens = len(tokenArray)

            response = self._api_call(prompt=textPrompt, max_tokens=min(300, 2048-num_tokens, num_tokens), temperature=0.0)
            print(response)
            summarizedText = response["choices"][0].message["content"]
            return summarizedText
        except Exception as e:
            print("Error in summarizer: ", e)
            raise e
        

