from fastapi import FastAPI, HTTPException, Depends
from fastapi.security import OAuth2PasswordBearer, OAuth2PasswordRequestForm
from pydantic import BaseModel
from typing import List, Optional
from datetime import datetime, timedelta
from jose import JWTError, jwt
import mysql.connector

SECRET_KEY = "lannion"
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 30

# Configuration de la connexion
mydb = mysql.connector.connect(
    host="mariadb_sae",
    user="saeuser",
    password="lannion",
    port=3306,
    database="saedb"
)

app = FastAPI()
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="token")

class NotesMatieres(BaseModel):
    noMat: int
    noNote: int
    Coefficient: int

class Token(BaseModel):
    access_token: str
    token_type: str
    statut: str

def create_access_token(data: dict, expires_delta: Optional[timedelta] = None):
    to_encode = data.copy()
    expire = datetime.utcnow() + (expires_delta if expires_delta else timedelta(minutes=15))
    to_encode.update({"exp": expire})
    return jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)

async def get_current_user(token: str = Depends(oauth2_scheme)):
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        username: str = payload.get("sub")
        if username is None:
            raise HTTPException(status_code=401, detail="Jeton invalide")
        return username
    except JWTError:
        raise HTTPException(status_code=401, detail="Jeton expiré ou invalide")

# --- AUTHENTIFICATION ---

@app.post("/token", response_model=Token)
async def login(form_data: OAuth2PasswordRequestForm = Depends()):
    # Utilisation d'un curseur avec dictionary=True pour manipuler les noms de colonnes
    cursor = mydb.cursor(dictionary=True)
    
    # Correction : table 'utilisateurs' et champs 'username'/'password' du formulaire
    sql = "SELECT EMAIL, STATUT FROM utilisateurs WHERE EMAIL = %s AND PASS = %s"
    cursor.execute(sql, (form_data.username, form_data.password))
    user = cursor.fetchone()
    cursor.close()

    if not user:
        raise HTTPException(
            status_code=400,
            detail="Identifiant ou mot de passe incorrect",
        )

    # Création du jeton avec le statut à l'intérieur
    access_token = create_access_token(
        data={"sub": user["EMAIL"], "statut": user["STATUT"]}
    )

    return {
        "access_token": access_token,
        "token_type": "bearer",
        "statut": user["STATUT"] # On renvoie le statut explicitement
    }

# --- ROUTES SÉCURISÉES ---
# Ajoutez Depends(get_current_user) pour protéger les routes

@app.get("/NoteMatieres/")
def get_all_notes_matieres(current_user: str = Depends(get_current_user)):
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

# Répétez l'ajout de "current_user: str = Depends(get_current_user)" pour POST, PUT et DELETE

# Filtrer par Matière
@app.get("/NoteMatieres/Matiere/{noMat}")
def get_notes_by_matiere(noMat: int, current_user: str = Depends(get_current_user)):
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
def get_notes_by_note(noNote: int, current_user: str = Depends(get_current_user)):
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
def create_note_matiere(noteMatieres: NotesMatieres, current_user: str = Depends(get_current_user)): 
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

@app.post("/token", response_model=Token)
async def login(form_data: OAuth2PasswordRequestForm = Depends()):
    cursor = mydb.cursor(dictionary=True)
    # On récupère l'email et le statut
    cursor.execute("SELECT EMAIL, STATUT FROM utilisateurs WHERE EMAIL = %s AND PASS = %s", 
                   (form_data.username, form_data.password))
    user = cursor.fetchone()
    cursor.close()
    
    if not user:
        raise HTTPException(status_code=400, detail="Incorrect email or password")
    
    # On crée le jeton
    access_token = create_access_token(data={"sub": user["EMAIL"], "statut": user["STATUT"]})
    
    # On retourne le jeton ET le statut pour le PHP
    return {
        "access_token": access_token, 
        "token_type": "bearer",
        "statut": user["STATUT"] # Ajout du statut ici
    }

# --- 3. PUT (Mise à jour) ---

@app.put("/NoteMatieres/{old_noMat}/{old_noNote}")
def update_note_matiere(old_noMat: int, old_noNote: int, new_data: NotesMatieres, current_user: str = Depends(get_current_user)):
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
def delete_note_matiere(noMat: int, noNote: int, current_user: str = Depends(get_current_user)):
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