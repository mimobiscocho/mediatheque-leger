# Dossier `lib/` — connecteur JDBC MySQL

Placez ici le pilote JDBC MySQL (**MySQL Connector/J**), nécessaire à
l'exécution de l'application.

1. Téléchargez le `.jar` « Platform Independent » depuis le site officiel :
   <https://dev.mysql.com/downloads/connector/j/>
   (par exemple `mysql-connector-j-8.4.0.jar`).
2. Copiez ce fichier `.jar` dans ce dossier `lib/`.

Les scripts `run.sh` (Linux/macOS) et `run.bat` (Windows) ajoutent
automatiquement tous les `.jar` de `lib/` au classpath.

> Le connecteur n'est requis qu'à **l'exécution** : la compilation
> (`javac`) fonctionne sans lui.
