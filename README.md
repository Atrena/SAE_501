# SAE_501


## VISA 1

Telecharger ce fichier : https://github.com/Atrena/SAE_501/releases/tag/v1.0

Docker est nécessaire pour faire fonctionner ce projet.

Pour utiliser ce projet, il suffit de le télécharger et de se mettre à la racine de celui-ci.

Ensuite, il faut exécuter cette commande :

```bash
docker build -t sae_501_v2 .
```
Vous pouvez changer "sae_501_v2" par le nom de votre choix.

Ensuite, il faut exécuter cette commande :
```bash
docker run -d -p 8080:80 --name SAE501 sae_501_v2
```

--- 

## VISA 2

Telecharger ce fichier : https://github.com/Atrena/SAE_501/releases/tag/v2.0

Docker est nécessaire pour faire fonctionner ce projet.

Pour utiliser ce projet, il suffit de le télécharger et de se mettre à la racine de celui-ci.

Ensuite, il faut exécuter cette commande :

```bash
docker build -f dockerfile_apache -t sae_apache .
```
Vous pouvez changer "sae_apache" par le nom de votre choix.

```bash
docker build -f dockerfile_mariadb -t sae_mariadb .
```
Vous pouvez changer "sae_mariadb" par le nom de votre choix.

Ensuite, il faut exécuter cette commande pour crée un reseaux:

```bash
docker network create network_sae
```

Ensuite on lance le conteneur apache dans le reseaux network_sae

```bash
docker run -dp 8080:80 --network network_sae sae_apache
```

Ensuite on lance le conteneur mariadb dans le reseaux network_sae

```bash
docker run -dp 3306:3306 --network network_sae -h mariadb_sae sae_mariadb
```

