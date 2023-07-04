import tiktoken

class TokenizerService:
    
    @staticmethod
    def limit_tokens(prompt, limit : int) -> str:
        encoding = tiktoken.get_encoding("cl100k_base")
        tokenized_prompt = encoding.encode(prompt)
        limited_tokenized_prompt = tokenized_prompt[0:limit]
        limited_textual_prompt = encoding.decode(limited_tokenized_prompt)
        return limited_textual_prompt
    