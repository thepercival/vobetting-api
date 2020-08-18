-- PRE PRE PRE doctrine-update =============================================================
ALTER TABLE pouleplaces rename places;

ALTER TABLE gamepouleplaces rename gameplaces;

ALTER TABLE games DROP FOREIGN KEY FK_FF232B31368C2673;

-- POST POST POST doctrine-update ===========================================================
-- teams
insert into teams( name, abbreviation, imageUrl, associationid ) ( select name, abbreviationDep, imageUrlDep, associationid from competitors );

-- externalteams
insert into externalteams( externalSystemid, importableObjectid, externalid ) ( select externalsystemid, ( select t.id from teams t join competitors c on c.associationid = t.associationid and c.name = t.name where c.id = externalcompetitors.importableobjectid ), externalid from externalcompetitors );

-- teamcompetitiors
insert into teamcompetitors(placeNr, pouleNr, registered, info, teamid, competitionid ) ( select p.number, po.number, c.registered, c.info, ( select t.id from teams t where c.associationid = t.associationid and c.name = t.name ), rn.competitionid from competitors c join places p on c.id = p.competitorid join poules po on po.id = p.pouleid join rounds r on r.id = po.roundid join roundnumbers rn on rn.id = r.numberid );

-- php bin/console.php app:create-default-planning-input --placesRange=2-4 --sendCreatePlanningMessage=true

-- CUSTOM IMPORT =============================
-- mysqldump -u fctoernooi_a_dba -p fctoernooiacc planninginputs plannings planningsports planningfields planningpoules planningplaces planningreferees planninggames planninggameplaces > planninginputs.sql
-- mysql -u fctoernooi_dba -p fctoernooi < planninginputs.sql
