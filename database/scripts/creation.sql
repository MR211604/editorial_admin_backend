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

create table editorial_documents
(
    idDocument int unsigned auto_increment
        primary key,
    title varchar(255) not null,
    slug varchar(191) not null,
    content longtext null,
    state tinyint(1) default 1 not null,
    sort_order int unsigned default 0 not null,
    created_at timestamp null,
    updated_at timestamp null,
    constraint editorial_documents_slug_unique unique (slug)
);

insert into editorial_documents (title, slug, content, state, sort_order, created_at, updated_at)
values
    ('Reglamento', 'reglamento', '', 1, 1, current_timestamp, current_timestamp),
    ('Procedimientos de publicación', 'procedimientos-de-publicacion', '', 1, 2, current_timestamp, current_timestamp),
    ('Publicacion para revista', 'publicacion-para-revista', '', 1, 3, current_timestamp, current_timestamp),
    ('Consejo editorial', 'consejo-editorial', '', 1, 4, current_timestamp, current_timestamp);

create table editorial_locations
(
    idLocation int unsigned auto_increment
        primary key,
    name varchar(255) not null,
    image varchar(2048) not null,
    address varchar(500) not null,
    mapsQuery varchar(500) not null,
    state tinyint(1) default 1 not null,
    sort_order int unsigned default 0 not null,
    created_at timestamp null,
    updated_at timestamp null
);

insert into editorial_locations (name, image, address, mapsQuery, state, sort_order, created_at, updated_at)
values
    ('Universidad Don Bosco', 'img/logos_ubicaciones/udb.png', 'Campus Soyapango y Campus Antiguo Cuscatlán', 'Universidad Don Bosco Campus Soyapango El Salvador', 1, 1, current_timestamp, current_timestamp),
    ('Librería y papelería UCA', 'img/logos_ubicaciones/libUCA.png', 'San Salvador – Campus UCA y Antiguo Cuscatlán – SOHO Cascadas', 'Librería y papelería UCA San Salvador El Salvador', 1, 2, current_timestamp, current_timestamp),
    ('La Ceiba', 'img/logos_ubicaciones/laceiba.png', 'La Ceiba Sucursales', 'La Ceiba librería El Salvador', 1, 3, current_timestamp, current_timestamp),
    ('Clásicos Roskil', 'img/logos_ubicaciones/roskil.png', '4a Avenida Sur, Santa Tecla', 'Clásicos Roskil Santa Tecla El Salvador', 1, 4, current_timestamp, current_timestamp),
    ('Gize Bakery', 'img/logos_ubicaciones/gize.png', 'Av. 14 de Julio, Reparto Los Héroes, San Salvador', 'Gize Bakery Reparto Los Héroes San Salvador El Salvador', 1, 5, current_timestamp, current_timestamp),
    ('La Biblioteca Café', 'img/logos_ubicaciones/bibliocafe.png', 'Presidente Plaza, BINAES – Centro Histórico', 'La Biblioteca Café Presidente Plaza BINAES San Salvador', 1, 6, current_timestamp, current_timestamp),
    ('American Coffeeshop', 'img/logos_ubicaciones/coffeeshop.png', 'Calle Chiltiupán #30, Ciudad Merliot', 'American Coffeeshop Calle Chiltiupán Ciudad Merliot El Salvador', 1, 7, current_timestamp, current_timestamp);

