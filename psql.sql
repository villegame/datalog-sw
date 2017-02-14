create schema temp_mon_schema;
create table temp_mon_schema.users (users_id serial primary key, users_name varchar(20), users_password varchar(73));
create table temp_mon_schema.devices (devices_id serial primary key, devices_name varchar(40), devices_source varchar(40), devices_sensor smallint, devices_enabled smallint, devices_type smallint, devices_screen smallint, devices_screen_order smallint);
create table temp_mon_schema.values (values_id serial primary key, devices_id integer not null, values_value numeric (9,2), values_time integer, constraint values_devices_id_fkey foreign key (devices_id) references temp_mon_schema.devices (devices_id) match simple on update cascade on delete cascade);
