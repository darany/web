import time
import random
from locust import HttpUser, task, between

class UtilisateurApi(HttpUser):
    host = 'http://localhost:8000/api/'
    weight = 4

    @task
    def list_rencontres(self):
        """ Utilisateur mobile qui liste les rencontres, puis affiche le détail de chaque rencontre"""
        response = self.client.get("rencontres")
        jsonResponse = response.json()
        total = jsonResponse['hydra:totalItems']
        # Contruire une liste d'id de rencontres "@id": "/api/rencontres/39"
        ids = [rencontre['@id'].split("/")[-1] for rencontre in jsonResponse['hydra:member']]
        while True:
            ii = random.randint(1, total-1)
            rencontre_id = ids[ii]
            self.client.get(f"rencontres/{rencontre_id}", name="Pooling score")
            time.sleep(30)

class UtilisateurWeb(HttpUser):
    host = 'http://localhost:8000'
    wait_time = between(1, 5)
    weight = 1

    @task(40)
    def homepage(self):
        """ Utilisateur web"""
        response = self.client.get("/")
 
    @task(100)
    def rencontres(self):
        """ Utilisateur web"""
        response = self.client.get("/rencontres")

    @task(10)
    def login(self):
        """ Utilisateur web"""
        response = self.client.get("/login")
