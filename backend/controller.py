from service import *
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from fastapi.middleware.cors import CORSMiddleware

class ChatMessage(BaseModel):
    message: str

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # For development only - restrict in production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.post("/chat")
async def chat_endpoint(chat_message: ChatMessage):
    try:
        bot_response = generate_bot_response(chat_message.message)
        return {"response": bot_response}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/extract-and-summarize")
def get_summarize(paper: dict):
    """
    Download a PDF from the provided URL (extracted from the input dictionary),
    then extract and summarize its text content.
    """
    try: 
        result = select_summary_from_database(paper['arxivId'])

        if result not in [None, []]:
            return result[0][6]
        else :
            result = extract_and_summarize_pdf(paper)
            print(result)
            return result
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
    

@app.post("/search_articles")
def search(query: dict):
    try: 
        initial_results = search_articles(query, max_results=10, categories=["cs.AI"])
        return initial_results
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))