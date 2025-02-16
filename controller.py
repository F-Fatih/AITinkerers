from service import *
from fastapi import FastAPI
from pydantic import BaseModel

class Message(BaseModel):
    text: str

app = FastAPI()

@app.get("/data")
def get_data():
    return get_data_from_database()

@app.post("/hello")
def post_hello(msg: Message):
    return {"message": f"You said: {msg.text}"}