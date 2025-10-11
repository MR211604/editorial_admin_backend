

insert into publishing_types(name, description) values ('Libro', 'Publicacion de tipo libro, contiene autor');
insert into publishing_types(name, description) values ('Revista', 'Publicacion de tipo revista, contiene categoria');
insert into publishing_types(name, description) values ('Manual', 'Publicacion de tipo manual, contiene categoria');

insert into languages(name, shortname) values ('Español', 'ES');
insert into languages(name, shortname) values ('English', 'EN');

#Insertaremos los roles predefinidos dentro del sistema
insert into rol(name, description) values ('Editor', 'Este rol permite editar registros');
insert into rol(name, description) values ('Administrador', 'Control total dentro de la aplicacion');


#INSERTANDO LAS CATEGORIAS PRINCIPALES DE LAS REVISTAS
insert into categories(title, description, main_category, state, is_featured, language_id)
values ('Científica', 'Revista cientifica', null, 0, 0,1);

insert into categories(title, description, main_category, state, is_featured, language_id)
values ('Ing-novación', 'Revista de innovacion en ingenieria', null, 0, 0, 1);

insert into categories(title, description, main_category, state, is_featured, language_id)
values ('Diá-logos', 'Revista de dialogos', null, 0, 0, 1);

insert into categories(title, description, main_category, state, is_featured, language_id)
values ('Teoría y Praxis', 'Revista de teoria y praxis', null, 0, 0, 1);


# INSERTANDO DETALLES DE TEORIA Y PRAXIS (a partir del menu de la pagina web)
insert into categories_details(about, editorial_board, policy, norms, process, contact, category_id, language_id)
values ('Estos son los detalles principales para la revista de teoria y praxis','','','','','', 1,1);
