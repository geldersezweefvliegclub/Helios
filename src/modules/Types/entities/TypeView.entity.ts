import {ViewColumn, ViewEntity} from 'typeorm';
import {IHeliosDatabaseEntity} from '../../../core/base/IHeliosDatabaseEntity';
import {booleanTransformer} from "../../../core/helpers/transformers/BooleanTransformer";

@ViewEntity({
    name: 'types_view',
    expression: `SELECT
    types.*
FROM
    ref_types as types
WHERE
    types.VERWIJDERD = 0
ORDER BY
    GROEP, SORTEER_VOLGORDE, ID;`
})
export class TypeViewEntity extends IHeliosDatabaseEntity{
    @ViewColumn()
    GROEP: number;

    @ViewColumn()
    CODE: string | null;

    @ViewColumn()
    EXT_REF: string | null;

    @ViewColumn()
    OMSCHRIJVING: string;

    @ViewColumn()
    SORTEER_VOLGORDE: number | null;

    @ViewColumn({transformer: booleanTransformer})
    READ_ONLY: boolean;

    @ViewColumn()
    BEDRAG: number | null;

    @ViewColumn()
    EENHEDEN: number | null;
}
