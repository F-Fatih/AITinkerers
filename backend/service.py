from database import *
import requests
import base64
import anthropic
from fastapi import HTTPException
import arxiv
from datetime import datetime
import json

CATEGORY_MAP = {
    'cs.AI': 'Artificial Intelligence',
    'cs.AR': 'Hardware Architecture',
    'cs.CC': 'Computational Complexity',
    'cs.CE': 'Computational Engineering, Finance, and Science',
    'cs.CG': 'Computational Geometry',
    'cs.CL': 'Computation and Language',
    'cs.CR': 'Cryptography and Security',
    'cs.CV': 'Computer Vision and Pattern Recognition',
    'cs.CY': 'Computers and Society',
    'cs.DB': 'Databases',
    'cs.DC': 'Distributed, Parallel, and Cluster Computing',
    'cs.DL': 'Digital Libraries',
    'cs.DM': 'Discrete Mathematics',
    'cs.DS': 'Data Structures and Algorithms',
    'cs.ET': 'Emerging Technologies',
    'cs.FL': 'Formal Languages and Automata Theory',
    'cs.GL': 'General Literature',
    'cs.GR': 'Graphics',
    'cs.GT': 'Computer Science and Game Theory',
    'cs.HC': 'Human-Computer Interaction',
    'cs.IR': 'Information Retrieval',
    'cs.IT': 'Information Theory',
    'cs.LG': 'Machine Learning',
    'cs.LO': 'Logic in Computer Science',
    'cs.MA': 'Multiagent Systems',
    'cs.MM': 'Multimedia',
    'cs.MS': 'Mathematical Software',
    'cs.NA': 'Numerical Analysis',
    'cs.NE': 'Neural and Evolutionary Computing',
    'cs.NI': 'Networking and Internet Architecture',
    'cs.OH': 'Other Computer Science',
    'cs.OS': 'Operating Systems',
    'cs.PF': 'Performance',
    'cs.PL': 'Programming Languages',
    'cs.RO': 'Robotics',
    'cs.SC': 'Symbolic Computation',
    'cs.SD': 'Sound',
    'cs.SE': 'Software Engineering',
    'cs.SI': 'Social and Information Networks',
    'cs.SY': 'Systems and Control'
}


def select_summary_from_database(id_arxiv: str) -> list:
    conn = connect_db()
    data = select_summary(conn, id_arxiv)
    close_db(conn)
    return data


def insert_article_to_database(article: dict) -> None: 
    conn = connect_db()
    insert_article(conn, article)
    close_db(conn)


def generate_bot_response(user_query: str) -> str:
    """Generate response based on user query (extend this with your NLP logic)"""
    user_query = user_query.lower()
    
    if "harmful" in user_query:
        return "It looks like you're interested in Harmful Speech. We have multiple papers on that topic."
    elif "ethics" in user_query:
        return "Check out 'Ethical Implications of Large Language Models'."
    elif "graph" in user_query:
        return "We have a paper on Graph Neural Networks for Text Classification."
    elif "nlp" in user_query or "natural language processing" in user_query:
        return "Our collection includes several papers on Advanced NLP Techniques."
    elif "date" in user_query or "publication" in user_query:
        return "Papers can be colored by publication date using the date fade mode."
    return "Thank you for your question! I can help you explore research papers about NLP, Ethics, Harmful Speech, and Graph Analysis."


def extract_and_summarize_pdf(paper: dict) -> str:
    """
    Download a PDF from the provided URL (extracted from the input dictionary),
    then extract and summarize its text content.
    """

    pdf_url = paper.get("pdf_url")
    if not pdf_url:
        raise HTTPException(
            status_code=400,
            detail="The request paper must include a 'arxivId' key."
        )
    print(pdf_url)
    
    # Fetch the PDF from the URL
    response = requests.get(pdf_url)
    if response.status_code != 200:
        raise HTTPException(
            status_code=400, 
            detail="Failed to download PDF from the provided URL."
        )
    
    # Encode the PDF content in base64
    pdf_bytes = response.content
    base64_pdf = base64.b64encode(pdf_bytes).decode()

    client = anthropic.Anthropic(
        api_key=****
    )

    # Send the request to your client API
    message = client.messages.create(
        model="claude-3-5-sonnet-20241022",
        max_tokens=1024,
        temperature=0,
        messages=[
            {
                "role": "user",
                "content": [
                    {
                        "type": "document",
                        "source": {
                            "type": "base64",
                            "media_type": "application/pdf",
                            "data": base64_pdf
                        }
                    },
                    {
                        "type": "text",
                        "text": (
                            "Summarize this PDF, highlighting the key topics covered, the methods used, "
                            "the reasoning behind those methods, the evaluation metrics, and the results. "
                            "Structure the summary with bullet points for clarity and structure. Additionally, "
                            "make sure to provide detailed explanations under each bullet point to ensure that "
                            "expert readers completely understand what was done and gain a deeper understanding of the paper "
                            "in order to be able to reproduce it or use it in their further research. Don't be too concise as "
                            "this would hurt the ability of the researcher to reproduce or understand properly the paper. "
                            "Your explanation should be exhaustive and detailed."
                        )
                    }
                ]
            }
        ]
    )

    client.close()
    
    insert_article_to_database({
        "title": paper["title"],
        "summary": message.content[0].text,
        "authors": paper["authors"],
        "arxivId": paper["arxivId"],
        "date": paper["date"],
        "abstract": paper["abstract"],
        "link": paper["url"]
    })

    return message.content[0].text


def search_articles(query, max_results=10, categories=None, date_from=None, date_to=None):
    """
    Perform an initial search based on the user's research interest.

    Parameters:
    - query (str): The search query string.
    - max_results (int): Maximum number of results to return.
    - categories (list, optional): List of arXiv category codes to filter results. Defaults to None.
    - date_from (str, optional): Start date in 'YYYYMMDD' format. Defaults to None.
    - date_to (str, optional): End date in 'YYYYMMDD' format. Defaults to None.

    Returns:
    - List of dictionaries containing paper details.
    """
    # Construct the search query
    search_query = f'all:"{query}"'
    
    if categories:
        category_query = ' OR '.join([f'cat:{cat}' for cat in categories])
        search_query = f'({category_query}) AND {search_query}'
    
    if date_from or date_to:
        date_from = date_from or '00000000'
        date_to = date_to or datetime.now().strftime('%Y%m%d')
        search_query += f' AND submittedDate:[{date_from}0000 TO {date_to}2359]'

    # Initialize the arXiv API search
    search = arxiv.Search(
        query=search_query,
        max_results=max_results,
        sort_by=arxiv.SortCriterion.SubmittedDate
    )
    print("This is search request"+search_query)
    # Initialize the arXiv API client
    client = arxiv.Client()

    # Retrieve and process the results
    results = []
    print("DD")
    for result in client.results(search):
        print(result)
        print("ff")
        paper = {
            'title': result.title,
            'authors': [author.name for author in result.authors],
            'abstract': result.summary,
            'published': result.published.strftime('%Y-%m-%d'),
            'url': result.entry_id,
            'primary_category': CATEGORY_MAP.get(result.primary_category, result.primary_category),
            'categories': [CATEGORY_MAP.get(cat, cat) for cat in result.categories],
            'arxivId': result.get_short_id(),
            'pdf_url': 'https://arxiv.org/pdf/'+result.get_short_id()
        }
        results.append(paper)

    print(client.results)
    print(results)
    return results