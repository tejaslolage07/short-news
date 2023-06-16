import openai
import tiktoken
import os

from dotenv import load_dotenv
load_dotenv()

openai.api_key = os.getenv("OPENAI_API_KEY")

def _generate_prompt(body):
    return f"""
Summarize the news article below, delimited by triple quotes. \
Respond in Japanese and in no more than 60 words.

Article: ```{body}```
"""

def tokenizedPrompt(text):
    #get the number of tokens in the article body
    encoding = tiktoken.get_encoding("r50k_base")
    return encoding.encode(text)

def _prepare_body(articleBody:str):
    #Strip the article body.
    return articleBody.strip()


def _api_call(prompt, max_tokens:int, temperature:float):
    messages = [{"role": "user" , "content": prompt}]
    response = openai.ChatCompletion.create(
        model="gpt-3.5-turbo",
        messages=messages,
        #lower the temperature to reduce randomness
        temperature=temperature,
        max_tokens=max_tokens,
    )
    return response

def chatGPTSummarizer(articleBody:str) -> str:
    try:
        articleBody = _prepare_body(articleBody)
        textPrompt = _generate_prompt(articleBody)
        print(textPrompt)

        tokenArray = tokenizedPrompt(textPrompt)[0:int(os.getenv("MAX_INPUT_TOKENS"))]
        num_tokens = len(tokenArray)

        response = _api_call(prompt=textPrompt, max_tokens=min(300, 2048-num_tokens, num_tokens), temperature=0.0)
        print(response)
        summarizedText = response["choices"][0].message["content"]
    except Exception as e:
        summarizedText = ""
        print("Error in summarizer: ", e)
    finally:
        return summarizedText
    
#Call this function with the article body as the argument
#chatGPTSummarizer("「R―1ぐらんぷり2018」王者で、視覚障がいを持つピン芸人・濱田祐太郎（33）が16日、自身のツイッターを更新。秘書だった男性を殴ったことを明らかにした自民党の高野光二郎参院議員について指摘した。 高野氏は昨年末に当時秘書だった20代男性を殴打し、鼻血を出させたと14日、国会内で記者団に説明。「飲み会で気合を入れるつもりで胸の辺りをたたくつもりが、鼻に当たった」と語っていた。 これについて濱田は")

