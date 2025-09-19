# SAE_501

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