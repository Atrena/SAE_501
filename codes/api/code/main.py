from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import mysql.connector

# Configuration de la connexion à la Base de Données
mydb = mysql.connector.connect(
    host="mariadb_sae",
    user="saeuser",
    password="lannion",
    port=3306,
    database="saedb"
)

app = FastAPI()

class NotesMatieres(BaseModel):
    noMat: int
    noNote: int
    Coefficient: int

# --- 1. GET (Lecture) ---

# Récupérer toutes les notes/matières
@app.get("/NoteMatieres/")
def get_all_notes_matieres():
    cursor = mydb.cursor(dictionary=True) 
    sql = """
        SELECT nm.noMat, m.NomMat, nm.noNote, n.NomNote, nm.Coefficient 
        FROM NotesMatieres nm
        JOIN Matieres m ON nm.noMat = m.NoMat
        JOIN Notes n ON nm.noNote = n.NoNote
    """
    cursor.execute(sql)
    result = cursor.fetchall()
    cursor.close()
    return result

# Filtrer par Matière
@app.get("/NoteMatieres/Matiere/{noMat}")
def get_notes_by_matiere(noMat: int):
    cursor = mydb.cursor(dictionary=True)
    sql = """
        SELECT nm.noMat, m.NomMat, nm.noNote, n.NomNote, nm.Coefficient 
        FROM NotesMatieres nm
        JOIN Matieres m ON nm.noMat = m.NoMat
        JOIN Notes n ON nm.noNote = n.NoNote
        WHERE nm.noMat = %s
    """
    cursor.execute(sql, (noMat,))
    result = cursor.fetchall()
    cursor.close()
    return result

# Filtrer par Note (Type de note)
@app.get("/NoteMatieres/Note/{noNote}")
def get_notes_by_note(noNote: int):
    cursor = mydb.cursor(dictionary=True)
    sql = """
        SELECT nm.noMat, m.NomMat, nm.noNote, n.NomNote, nm.Coefficient 
        FROM NotesMatieres nm
        JOIN Matieres m ON nm.noMat = m.NoMat
        JOIN Notes n ON nm.noNote = n.NoNote
        WHERE nm.noNote = %s
    """
    cursor.execute(sql, (noNote,))
    result = cursor.fetchall()
    cursor.close()
    return result

# --- 2. POST (Création) ---

@app.post("/NoteMatieres/")
def create_note_matiere(noteMatieres: NotesMatieres): 
    cursor = mydb.cursor()
    sql = "INSERT INTO NotesMatieres (noMat, noNote, Coefficient) VALUES (%s, %s, %s)"
    val = (noteMatieres.noMat, noteMatieres.noNote, noteMatieres.Coefficient)
    
    try:
        cursor.execute(sql, val)
        mydb.commit()
        return {"message": "Insertion réussie", "data": noteMatieres}
    except mysql.connector.Error as err:
        raise HTTPException(status_code=400, detail=str(err))
    finally:
        cursor.close()

# --- 3. PUT (Mise à jour) ---

@app.put("/NoteMatieres/{old_noMat}/{old_noNote}")
def update_note_matiere(old_noMat: int, old_noNote: int, new_data: NotesMatieres):
    cursor = mydb.cursor()
    sql = """
    UPDATE NotesMatieres 
    SET noMat = %s, noNote = %s, Coefficient = %s 
    WHERE noMat = %s AND noNote = %s
    """
    val = (new_data.noMat, new_data.noNote, new_data.Coefficient, old_noMat, old_noNote)

    try:
        cursor.execute(sql, val)
        mydb.commit()
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Aucune ligne mise à jour (IDs incorrects ?)")
        return {"message": "Modification réussie", "nouvelles_valeurs": new_data}
    except mysql.connector.Error as err:
        raise HTTPException(status_code=400, detail=str(err))
    finally:
        cursor.close()

# --- 4. DELETE (Suppression) ---

@app.delete("/NoteMatieres/{noMat}/{noNote}")
def delete_note_matiere(noMat: int, noNote: int):
    cursor = mydb.cursor()
    sql = "DELETE FROM NotesMatieres WHERE noMat = %s AND noNote = %s"
    val = (noMat, noNote)
    
    try:
        cursor.execute(sql, val)
        mydb.commit()
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Ligne introuvable, rien n'a été supprimé")
        return {"message": f"Suppression réussie pour matière {noMat} et note {noNote}"}
    except mysql.connector.Error as err:
        raise HTTPException(status_code=400, detail=str(err))
    finally:
        cursor.close()