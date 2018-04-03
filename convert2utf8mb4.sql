-- use this script to convert your nrdb database to utf8mb4
-- there are two things you must do before running this:
-- 1) make a backup of your database. mysqldump is your friend.
-- 2) change the "alter database" statement to use your database name (check "database_name" in app/config/parameters.yml)
-- then run it, e.g. using mysqladmin -uUSER -pPASS DBNAME <convert2utf8mb4.sql

alter database nrdb character set = utf8mb4 collate = utf8mb4_unicode_ci;

alter table client convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table claim convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table type convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table legality convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table card convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table moderation convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table decklist convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table favorite convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table vote convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table refresh_token convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table review convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table reviewvote convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table prebuiltslot convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table ruling convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table pack convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table highlight convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table auth_code convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table prebuilt convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table comment convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table tournament convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table side convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table faction convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table cycle convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table deck convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table reviewcomment convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table mwl convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table user convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table follow convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table rotation convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table rotation_cycle convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table decklistslot convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table access_token convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table modflags convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table deckchange convert to character set utf8mb4 collate utf8mb4_unicode_ci;
alter table deckslot convert to character set utf8mb4 collate utf8mb4_unicode_ci;

alter table client default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table claim default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table type default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table legality default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table card default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table moderation default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table decklist default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table favorite default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table vote default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table refresh_token default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table review default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table reviewvote default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table prebuiltslot default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table ruling default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table pack default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table highlight default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table auth_code default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table prebuilt default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table comment default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table tournament default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table side default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table faction default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table cycle default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table deck default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table reviewcomment default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table mwl default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table user default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table follow default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table rotation default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table rotation_cycle default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table decklistslot default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table access_token default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table modflags default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table deckchange default character set utf8mb4 collate utf8mb4_unicode_ci;
alter table deckslot default character set utf8mb4 collate utf8mb4_unicode_ci;
