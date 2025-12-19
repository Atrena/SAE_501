from fastapi import FastAPI, HTTPException, Depends, status
from fastapi.security import OAuth2PasswordBearer, OAuth2PasswordRequestForm
from pydantic import BaseModel
import mysql.connector
from jose import JWTError, jwt
from datetime import datetime, timedelta
from typing import List, Optional

# --- CONFIGURATION SÉCURITÉ JWT ---
SECRET_KEY = "Lannion"
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 30

# Schéma pour récupérer le token dans le header "Authorization: Bearer <token>"
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="token")

# --- CONNEXION BDD ---
def get_db_connection():
    return mysql.connector.connect(
        host="mariadb_sae",
        user="saeuser",
        password="lannion",
        port=3306,
        database="saedb"
    )

app = FastAPI()

# --- MODÈLES ---
class NotesMatieres(BaseModel):
    noMat: int
    noNote: int
    Coefficient: int

class Token(BaseModel):
    access_token: str
    token_type: str
    statut: str

# --- FONCTIONS UTILITAIRES JWT ---
def create_access_token(data: dict, expires_delta: Optional[timedelta] = None):
    to_encode = data.copy()
    if expires_delta:
        expire = datetime.utcnow() + expires_delta
    else:
        expire = datetime.utcnow() + timedelta(minutes=15)
    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)
    return encoded_jwt

# --- ROUTE D'AUTHENTIFICATION (VISA 4) ---
@app.post("/token", response_model=Token)
async def login_for_access_token(form_data: OAuth2PasswordRequestForm = Depends()):
    """
    Vérifie les identifiants et délivre un jeton JWT.
    """
    db = get_db_connection()
    cursor = db.cursor(dictionary=True)
    
    # Recherche de l'utilisateur (on utilise les colonnes de votre BDD : EMAIL, PASS, STATUT)
    query = "SELECT EMAIL, PASS, STATUT FROM utilisateurs WHERE EMAIL = %s"
    cursor.execute(query, (form_data.username,))
    user = cursor.fetchone()
    cursor.close()
    db.close()

    if not user or form_data.password != user['PASS']:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Email ou mot de passe incorrect",
            headers={"WWW-Authenticate": "Bearer"},
        )

    # Création du jeton avec l'email et le statut
    access_token_expires = timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    access_token = create_access_token(
        data={"sub": user['EMAIL'], "statut": user['STATUT']},
        expires_delta=access_token_expires
    )
    
    return {
        "access_token": access_token, 
        "token_type": "bearer", 
        "statut": user['STATUT']
    }

# --- ROUTES CRUD PROTÉGÉES (VISA 3 & 4) ---

@app.get("/NoteMatieres/")
def get_all_notes_matieres(token: str = Depends(oauth2_scheme)):
    """Récupère toutes les notes (nécessite un token JWT)"""
    db = get_db_connection()
    cursor = db.cursor(dictionary=True) 
    sql = """
        SELECT nm.noMat, m.NomMat, nm.noNote, n.NomNote, nm.Coefficient 
        FROM NotesMatieres nm
        JOIN Matieres m ON nm.noMat = m.NoMat
        JOIN Notes n ON nm.noNote = n.NoNote
    """
    cursor.execute(sql)
    result = cursor.fetchall()
    cursor.close()
    db.close()
    return result

@app.post("/NoteMatieres/", status_code=201)
def create_note_matiere(noteMatieres: NotesMatieres, token: str = Depends(oauth2_scheme)):
    db = get_db_connection()
    cursor = db.cursor()
    sql = "INSERT INTO NotesMatieres (noMat, noNote, Coefficient) VALUES (%s, %s, %s)"
    val = (noteMatieres.noMat, noteMatieres.noNote, noteMatieres.Coefficient)
    try:
        cursor.execute(sql, val)
        db.commit()
        return {"message": "Note ajoutée avec succès"}
    except mysql.connector.Error as err:
        raise HTTPException(status_code=400, detail=str(err))
    finally:
        cursor.close()
        db.close()

@app.put("/NoteMatieres/{noMat}/{noNote}")
def update_note_matiere(noMat: int, noNote: int, noteMatieres: NotesMatieres, token: str = Depends(oauth2_scheme)):
    db = get_db_connection()
    cursor = db.cursor()
    sql = "UPDATE NotesMatieres SET Coefficient = %s WHERE noMat = %s AND noNote = %s"
    val = (noteMatieres.Coefficient, noMat, noNote)
    try:
        cursor.execute(sql, val)
        db.commit()
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Ligne non trouvée")
        return {"message": "Mise à jour réussie"}
    except mysql.connector.Error as err:
        raise HTTPException(status_code=400, detail=str(err))
    finally:
        cursor.close()
        db.close()

@app.delete("/NoteMatieres/{noMat}/{noNote}")
def delete_note_matiere(noMat: int, noNote: int, token: str = Depends(oauth2_scheme)):
    db = get_db_connection()
    cursor = db.cursor()
    sql = "DELETE FROM NotesMatieres WHERE noMat = %s AND noNote = %s"
    try:
        cursor.execute(sql, (noMat, noNote))
        db.commit()
        if cursor.rowcount == 0:
            raise HTTPException(status_code=404, detail="Ligne non trouvée")
        return {"message": "Suppression réussie"}
    except mysql.connector.Error as err:
        raise HTTPException(status_code=400, detail=str(err))
    finally:
        cursor.close()
        db.close()