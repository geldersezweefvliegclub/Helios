import {ViewColumn, ViewEntity} from 'typeorm';
import {IHeliosDatabaseEntity} from '../../../core/base/IHeliosDatabaseEntity';
import {booleanTransformer} from "../../../core/helpers/transformers/BooleanTransformer";

@ViewEntity({
    name: 'leden_view',
    expression: `
        SELECT l.*,
               IF((SELECT count(*)
                   FROM oper_progressie
                   WHERE LID_ID = l.ID
                     AND COMPETENTIE_ID = 0) = 0, 0, 1) AS PAX,
               t.OMSCHRIJVING                           AS LIDTYPE,
               s.OMSCHRIJVING                           AS STATUS,
               z.NAAM                                   AS ZUSTERCLUB,
               b.NAAM                                   AS BUDDY,
               b2.NAAM                                  AS BUDDY2
        FROM ref_leden l
                 LEFT JOIN ref_types t ON (l.LIDTYPE_ID = t.ID)
                 LEFT JOIN ref_types s ON (l.STATUSTYPE_ID = s.ID)
                 LEFT JOIN ref_leden z ON (l.ZUSTERCLUB_ID = z.ID)
                 LEFT JOIN ref_leden b ON (l.BUDDY_ID = b.ID)
                 LEFT JOIN ref_leden b2 ON (l.BUDDY_ID = b2.ID)
        WHERE l.VERWIJDERD = 0
        ORDER BY ACHTERNAAM, VOORNAAM`
})
export class LedenViewEntity extends IHeliosDatabaseEntity {
    @ViewColumn()
    ID: number;

    @ViewColumn()
    NAAM: string | null;

    @ViewColumn()
    VOORNAAM: string | null;

    @ViewColumn()
    TUSSENVOEGSEL: string | null;

    @ViewColumn()
    ACHTERNAAM: string | null;

    @ViewColumn()
    ADRES: string | null;

    @ViewColumn()
    POSTCODE: string | null;

    @ViewColumn()
    WOONPLAATS: string | null;

    @ViewColumn()
    TELEFOON: string | null;

    @ViewColumn()
    MOBIEL: string | null;

    @ViewColumn()
    NOODNUMMER: string | null;

    @ViewColumn()
    EMAIL: string | null;

    @ViewColumn()
    LIDNR: string | null;

    @ViewColumn()
    LIDTYPE_ID: number | null;

    @ViewColumn()
    STATUSTYPE_ID: number | null;

    @ViewColumn()
    ZUSTERCLUB_ID: number | null;

    @ViewColumn()
    BUDDY_ID: number | null;

    @ViewColumn()
    BUDDY_ID2: number | null;

    @ViewColumn({transformer: booleanTransformer})
    LIERIST: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    LIERIST_IO: boolean;

    @ViewColumn({transformer: booleanTransformer})
    STARTLEIDER: boolean;

    @ViewColumn({transformer: booleanTransformer})
    INSTRUCTEUR: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    CIMT: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    DDWV_CREW: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    DDWV_BEHEERDER: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    BEHEERDER: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    STARTTOREN: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    ROOSTER: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    SLEEPVLIEGER: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    RAPPORTEUR: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    GASTENVLIEGER: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    TECHNICUS: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    CLUBBLAD_POST: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    ZELFSTART_ABONNEMENT: boolean | null;

    @ViewColumn()
    MEDICAL: Date | null;

    @ViewColumn()
    GEBOORTE_DATUM: Date | null;

    @ViewColumn()
    INLOGNAAM: string | null;

    @ViewColumn()
    WACHTWOORD: string | null;

    @ViewColumn()
    SECRET: string | null;

    @ViewColumn({transformer: booleanTransformer})
    AUTH: boolean | null;

    @ViewColumn()
    AVATAR: string | null;

    @ViewColumn({transformer: booleanTransformer})
    STARTVERBOD: boolean | null;

    @ViewColumn({transformer: booleanTransformer})
    PRIVACY: boolean | null;

    @ViewColumn()
    SLEUTEL1: string | null;

    @ViewColumn()
    SLEUTEL2: string | null;

    @ViewColumn()
    KNVVL_LIDNUMMER: string | null;

    @ViewColumn()
    BREVET_NUMMER: string | null;

    @ViewColumn({transformer: booleanTransformer})
    EMAIL_DAGINFO: string | null;

    @ViewColumn()
    OPMERKINGEN: string | null;

    @ViewColumn()
    TEGOED: number | null;

    @ViewColumn({transformer: booleanTransformer})
    VERWIJDERD: boolean | null;

    @ViewColumn()
    LAATSTE_AANPASSING: Date | null;

    @ViewColumn({transformer: booleanTransformer})
    PAX: boolean | null;

    @ViewColumn()
    LIDTYPE: string | null;

    @ViewColumn()
    STATUS: string | null;

    @ViewColumn()
    ZUSTERCLUB: string | null;

    @ViewColumn()
    BUDDY: string | null;

    @ViewColumn()
    BUDDY2: string | null;


}
