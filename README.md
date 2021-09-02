# FetchMagentoData


Several commands for easier use:

- docker-compose up --build
- docker exec -it laravelfetchdata_php_1 bash
- cd /code (now you are in docker php container every comand should be executed from here)


Routes:

- /products (insert all products in mongo)
- /categories (insert all categories in mongo)


Laravel project itself is located in the code folder so it can be used without a docker.

Lot of custom attributes in controller need to be changed depending of project, or make them configurable.



