from service import *
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel

class ChatMessage(BaseModel):
    message: str

app = FastAPI()

@app.post("/chat")
async def chat_endpoint(chat_message: ChatMessage):
    try:
        bot_response = generate_bot_response(chat_message.message)
        return {"response": bot_response}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))