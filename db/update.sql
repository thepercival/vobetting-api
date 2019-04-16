update externalleagues set externalid = '2003' where externalsystemid = ( select id from externalsystems where name = 'Football Data') and externalid = 'DED';

-- only for dev
-- delete from externalleagues where externalsystemid = ( select id from externalsystems where name = 'Football Data') and externalid <> 'DED';
-- delete from externalseasons where externalsystemid = ( select id from externalsystems where name = 'Football Data')  and externalid = '2017';
