from database import *

def get_data_from_database():
    conn = connect_db()

    data = get_data(conn)

    close_db(conn)

    return data

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