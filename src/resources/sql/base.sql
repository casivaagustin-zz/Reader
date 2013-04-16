CREATE TABLE POST(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title varchar(255),
        body text,
        post_date integer,
        author varchar(255),
        link text,
        hash varchar(255),
        source_id integer
        , date integer default 0);

CREATE TABLE READ(
        post_id integer,
        user_id integer,
        date_time integer,
        PRIMARY KEY (post_id, user_id)
        );

CREATE TABLE SOURCE(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name varchar(100),
        type varchar(20), 
        hash varchar(100), 
        url varchar(500), 
        enabled boolean default true, 
        last_update integer default 0, 
        fail integer default 0);

CREATE TABLE USER(
        ID INTEGER PRIMARY KEY AUTOINCREMENT,
        name varcahr(100),
        password varchar(100)
        );

CREATE TABLE USER_SOURCES(
        user_id integer,
        source_id integer,
        PRIMARY KEY (source_id, user_id)
        );
