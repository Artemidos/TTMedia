create table if not exists khlystov_currencycourses
(
    ID int(10) not null auto_increment,
    CODE varchar(50) not null,
    DATE datetime not null default current_timestamp,
    COURSE float not null,
    PRIMARY KEY (ID)
)