drop table if exists `riddles`;
drop table if exists `submissions`;
drop table if exists `assignments`;
drop table if exists `chat`;
drop table if exists `users`;

-- create table for users
create table `users` (
  `id` int not null auto_increment primary key,
    `role` varchar(255) not null default 'student',
  `username` varchar(255) not null,
    `password` varchar(255) not null,
    `name` varchar(255) not null,
    `email` varchar(255),
    `phone` varchar(255),
    `avatar` varchar(255)
);

-- create some data for testing (password is a sha256 hash of the string '123456a@A')
insert into `users` (`role`, `username`, `password`, `name`) values ('student', 'student1', 'c0b1d84fd16d13bf53eb5e8b2c197565b569855240b59ad4b7b812df946534c4', 'Student 1');
insert into `users` (`role`, `username`, `password`, `name`) values ('student', 'student2', 'c0b1d84fd16d13bf53eb5e8b2c197565b569855240b59ad4b7b812df946534c4', 'Student 2');
insert into `users` (`role`, `username`, `password`, `name`) values ('teacher', 'teacher1', 'c0b1d84fd16d13bf53eb5e8b2c197565b569855240b59ad4b7b812df946534c4', 'Teacher 1');
insert into `users` (`role`, `username`, `password`, `name`) values ('teacher', 'teacher2', 'c0b1d84fd16d13bf53eb5e8b2c197565b569855240b59ad4b7b812df946534c4', 'Teacher 2');

-- chat box
create table `chat` (
  `id` int not null auto_increment primary key,
  `sender` int not null,
  `receiver` int not null,
  `message` text not null,
  `created_at` timestamp not null default current_timestamp
);
-- make foreign key
alter table `chat` add foreign key (`sender`) references `users` (`id`);
alter table `chat` add foreign key (`receiver`) references `users` (`id`);

-- assignments, teacher can create assignment, student can submit assignment
create table `assignments` (
  `id` int not null auto_increment primary key,
  `teacher` int not null,
  `title` varchar(255) not null,
  `description` text not null,
  `file` varchar(255)
);

-- make foreign key
alter table `assignments` add foreign key (`teacher`) references `users` (`id`);

-- student assignment submissions
create table `submissions` (
  `id` int not null auto_increment primary key,
  `assignment_id` int not null,
  `student` int not null,
  `file` varchar(255),
  `created_at` timestamp not null default current_timestamp
);

-- make foreign key
alter table `submissions` add foreign key (`assignment_id`) references `assignments` (`id`);
alter table `submissions` add foreign key (`student`) references `users` (`id`);

-- riddles table, teacher can create riddles, student can submit riddles
create table `riddles` (
  `id` int not null auto_increment primary key,
  `teacher` int not null,
  `title` varchar(255) not null,
  `description` text not null,
  `file` varchar(255)
);

-- make foreign key
alter table `riddles` add foreign key (`teacher`) references `users` (`id`);