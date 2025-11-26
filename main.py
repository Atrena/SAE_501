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

class Matieres(BaseModel):
    noMat:int
    nomMat:str
    Prof:str
    
class NotesMatieres(BaseModel):
    noNote:int
    noMat:int
    Coefficient:float
    
class Notes(BaseModel):
    noNote:int
    nomNote:str
   
    
app = FastAPI()

@app.get("/Notes/{noNote}")
def get(noNote:int): 
    rq = f"SELECT Notes.nomNote, Matieres.nomMat, Matieres.Prof, NotesMatieres.Coefficient FROM NotesMatieres INNER JOIN Notes ON Notes.noNote = NotesMatieres.noNote INNER JOIN Matieres ON Matieres.noMat = NotesMatieres.noMat WHERE Notes.noNote = {noNote};"
    cursor = mydb.cursor()
    cursor.execute(rq)
    result = cursor.fetchall()
    tab:tuple = []
    for e in result :
        tab.append(e)
    return tab
    
@app.get("/Matieres/{noMat}")
def get(noMat:int): 
    rq = f"SELECT Notes.NomNote, Matieres.NomMat, Matieres.Prof, NotesMatieres.Coefficient FROM NotesMatieres INNER JOIN Notes ON Notes.NoNote = NotesMatieres.noNote INNER JOIN Matieres ON Matieres.NoMat = NotesMatieres.noMat WHERE Matieres.NoMat = {noMat};"
    cursor = mydb.cursor()
    cursor.execute(rq)
    result = cursor.fetchall()
    tab:tuple = []
    for e in result :
        tab.append(e)
    return tab
    
@app.post("/NoteMatieres/")
def post(noteMatieres: NotesMatieres): 
    rq = f"INSERT INTO NotesMatieres VALUES ({noteMatieres.noMat}, {noteMatieres.noNote}, {noteMatieres.Coefficient})"
    cursor = mydb.cursor()
    cursor.execute(rq)
    return "Insertion reussie"

@app.put("/NoteMatieres/")
def put(noteMatieres: NotesMatieres): 
    rq = f"UPDATE NotesMatieres SET noNote={noteMatieres.noNote}, noMat={noteMatieres.noMat}, Coefficient={noteMatieres.Coefficient} WHERE noNote='{noteMatieres.noNote}' AND noMat='{noteMatieres.noMat}' AND Coefficient='{noteMatieres.Coefficient}'"
    cursor = mydb.cursor()
    cursor.execute(rq)
    return "Insertion reussie"
