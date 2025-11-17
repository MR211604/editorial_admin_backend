create table authors
(
    id       int auto_increment
        primary key,
    name     varchar(150) null,
    lastname varchar(150) null
);

create table categories_types
(
    id   int auto_increment
        primary key,
    name varchar(255) null
);

create table languages
(
    id        int auto_increment
        primary key,
    name      varchar(55) null,
    shortname varchar(55) null
);

create table categories
(
    id            int auto_increment
        primary key,
    title         varchar(55) not null,
    description   varchar(55) null,
    main_category int         null,
    state         tinyint     null,
    is_featured   tinyint(1)  null,
    language_id   int         null,
    category_type int         null comment 'tipo de categoria',
    constraint categories_categories_types_id_fk
        foreign key (category_type) references categories_types (id),
    constraint categories_ibfk_1
        foreign key (language_id) references languages (id)
);

create index language_id
    on categories (language_id);

create table categories_details
(
    id              int auto_increment
        primary key,
    about           text null,
    editorial_board text null,
    policy          text null,
    norms           text null,
    process         text null,
    contact         text null,
    category_id     int  null,
    language_id     int  null,
    constraint categories_details_ibfk_1
        foreign key (category_id) references categories (id),
    constraint categories_details_ibfk_2
        foreign key (language_id) references languages (id)
);

create index category_id
    on categories_details (category_id);

create index language_id
    on categories_details (language_id);

create table publishing_types
(
    id          int auto_increment
        primary key,
    name        varchar(150) null,
    description text         null
);

create table publishings
(
    id              int auto_increment
        primary key,
    title           varchar(300)      null,
    cover           varchar(150)      null,
    age             varchar(150)      null,
    volume          int               null,
    volume_date     date              null,
    publish_code    varchar(50)       null,
    is_new_arrival  tinyint(1)        null,
    publish_date    date              null,
    state           tinyint           null,
    publisher       varchar(100)      null,
    stock           int               null,
    author_id       int               null,
    language_id     int               null,
    category_id     int               null,
    publishing_type int               null,
    age_number      int               null comment 'numero de epoca',
    pages           int               null comment 'numero de paginas de la publicacion',
    URL             text              null comment 'URL del recurso (usado en libros)',
    is_archived     tinyint default 0 not null comment 'Campo que permite evaluar si una publicacion esta archivada o no',
    constraint publishings_authors_id_fk
        foreign key (author_id) references authors (id),
    constraint publishings_ibfk_1
        foreign key (language_id) references languages (id),
    constraint publishings_ibfk_2
        foreign key (category_id) references categories (id),
    constraint publishings_ibfk_3
        foreign key (publishing_type) references publishing_types (id)
);

create table authors_publishings
(
    publishing_id int null,
    author_id     int null,
    constraint authors_publishings_ibfk_1
        foreign key (publishing_id) references publishings (id),
    constraint authors_publishings_ibfk_2
        foreign key (author_id) references authors (id)
);

create index author_id
    on authors_publishings (author_id);

create index publishing_id
    on authors_publishings (publishing_id);

create table publishing_details
(
    id             int auto_increment
        primary key,
    summary        text         null,
    URL            text         null,
    repository_URL text         null,
    publishing_id  int          null,
    language_id    int          null,
    title          varchar(100) null,
    constraint publishing_details_ibfk_1
        foreign key (publishing_id) references publishings (id),
    constraint publishing_details_ibfk_2
        foreign key (language_id) references languages (id)
);

create index language_id
    on publishing_details (language_id);

create index publishing_id
    on publishing_details (publishing_id);

create table publishing_translations
(
    id            int auto_increment
        primary key,
    publishing_id int          not null,
    language_id   int          not null,
    title         varchar(300) null,
    URL           text         null,
    summary       text         null comment 'Resumen de los detalles de un articulo de una revista',
    constraint publishing_translations_ibfk_1
        foreign key (publishing_id) references publishings (id),
    constraint publishing_translations_ibfk_2
        foreign key (language_id) references languages (id)
);

create index language_id
    on publishing_translations (language_id);

create index publishing_id
    on publishing_translations (publishing_id);

create index category_id
    on publishings (category_id);

create index language_id
    on publishings (language_id);

create index publishing_type
    on publishings (publishing_type);

create table rol
(
    id          int auto_increment
        primary key,
    name        varchar(55) null,
    description varchar(55) null
);

create table users
(
    id              int auto_increment
        primary key,
    name            varchar(55)  not null,
    password        varchar(255) not null,
    active          tinyint(1)   null,
    join_date       datetime     null,
    last_login_date datetime     null,
    rol_id          int          null,
    token           varchar(45)  null comment 'token de usuario o username',
    constraint users_ibfk_1
        foreign key (rol_id) references rol (id)
);

create table rol_users
(
    user_id int null,
    rol_id  int null,
    constraint rol_users_ibfk_1
        foreign key (user_id) references users (id),
    constraint rol_users_ibfk_2
        foreign key (rol_id) references rol (id)
);

create index rol_id
    on rol_users (rol_id);

create index user_id
    on rol_users (user_id);

create index rol_id
    on users (rol_id);

