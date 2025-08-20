drop database if exists editorial_udb;

create database editorial_udb;

use editorial_udb;

create table rol
(
    id          int primary key auto_increment,
    name        varchar(55),
    description varchar(55)
);

create table users
(
    id              int primary key auto_increment,
    name            varchar(55)  not null,
    password        varchar(255) not null,
    active          boolean,
    join_date       date,
    last_login_date date,
    rol_id          int,
    foreign key (rol_id) references rol (id)
);


create table privileges
(
    id             int primary key auto_increment,
    privilege_name varchar(50),
    is_active      boolean
);

create table rol_privileges
(
    rol_id       int,
    privilege_id int,
    foreign key (rol_id) references rol (id),
    foreign key (privilege_id) references privileges (id)
);


create table languages
(
    id        int primary key auto_increment,
    name      varchar(55),
    shortname varchar(55)
);

create table categories
(
    id            int primary key auto_increment,
    title         varchar(55) not null,
    description   varchar(55),
    main_category int,
    state         tinyint,
    is_featured   boolean,
    language_id   int,
    category_type tinyint(1),
    foreign key (language_id) references languages (id)
);

/* Tabla usada para guardar los detalles dentro las categorias (usado en revistas) */
create table categories_details
(
    id              int primary key auto_increment,
    description     text,
    editorial_board text,
    policy          text,
    norms           text,
    process         text,
    contact         text,
    category_id     int,
    language_id     int,
    foreign key (category_id) references categories (id),
    foreign key (language_id) references languages (id)
);


create table publishing_types
(
    id          int primary key auto_increment,
    name        varchar(150),
    description text
);

create table authors
(
    id       int primary key auto_increment,
    name     varchar(150),
    lastname varchar(150)
);

create table publishings
(
    id              int primary key auto_increment,
    title           varchar(150),
    cover           varchar(150),
    age             varchar(150),
    age_number      int,
    volume          int,
    volume_date     date,
    pages           int,
    publish_code    varchar(50),
    is_new_arrival  boolean,
    publish_date    date,
    state           tinyint,
    publisher       varchar(100),
    stock           int,
    author_id       int,
    URL             text,
    language_id     int,
    category_id     int,
    publishing_type int,
    foreign key (language_id) references languages (id),
    foreign key (category_id) references categories (id),
    foreign key (publishing_type) references publishing_types (id),
    foreign key (author_id) references authors (id)
);

create table publishing_details
(
    id             int primary key auto_increment,
    summary        text,
    URL            text,
    title          varchar(100),
    repository_URL text,
    publishing_id  int,
    language_id    int,
    foreign key (publishing_id) references publishings (id),
    foreign key (language_id) references languages (id)
);

create table authors_publishings
(
    publishing_id int,
    author_id     int,
    foreign key (publishing_id) references publishings (id),
    foreign key (author_id) references authors (id)
);