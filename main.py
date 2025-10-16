from fastapi import FastAPI
from pydantic import BaseModel

import mysql.connector

mydb = mysql.connector.connect(
    host="localhost",
    user="saeuser",
    password="lannion",
    port=3306,
    database="saedb"
)

class Notes(BaseModel):
    noNote:int
    nomNote:str
   
    
app = FastAPI()

@app.get("/Notes/{NoNote}")
def get(NoNote): 
    rq = f"SELECT * FROM Notes WHERE noNote = {NoNote}"
    cursor = mydb.cursor()
    cursor.execute(rq)
    result = cursor.fetchall()
    for e in result :
        return e