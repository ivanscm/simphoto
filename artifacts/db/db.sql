create table images
(
    slug        text
        constraint images_pk
            primary key,
    title       text,
    description text
);

create unique index images_slug_uindex
    on images (slug);

create table tags
(
    slug  text
        constraint tags_pk
            primary key,
    title text
);

create unique index tags_slug_uindex
    on tags (slug);

create table tags2images
(
    image text not null
        references images,
    tag   text not null
        references tags
);

