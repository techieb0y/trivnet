CREATE FUNCTION sp_upsert_persondata(pid INT, dt INT, val INT) RETURNS VOID AS
$$
BEGIN
    LOOP
        -- first try to update the key
        UPDATE persondata SET value = val WHERE datatype = dt AND personid = pid;
        IF found THEN
            RETURN;
        END IF;
        -- not there, so try to insert the key
        -- if someone else inserts the same key concurrently,
        -- we could get a unique-key failure
        BEGIN
            INSERT INTO persondata(personid,datatype,value) VALUES (pid, dt, val);
            RETURN;
        EXCEPTION WHEN unique_violation THEN
            -- do nothing, and loop to try the UPDATE again
        END;
    END LOOP;
END;
$$
LANGUAGE plpgsql;
