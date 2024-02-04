import {
    Entity,
    PrimaryGeneratedColumn,
    Column,
    Index,
    UpdateDateColumn
} from 'typeorm';

@Entity('ref_types')
@Index('GROEP', ['GROEP'])
@Index('VERWIJDERD', ['VERWIJDERD'])
export class TypeEntity {
    @PrimaryGeneratedColumn({ type: 'mediumint', unsigned: true })
    ID: number;

    @Column({ type: 'smallint', unsigned: true })
    GROEP: number;

    @Column({ type: 'varchar', length: 10, nullable: true })
    CODE: string | null;

    @Column({ type: 'varchar', length: 25, nullable: true })
    EXT_REF: string | null;

    @Column({ type: 'varchar', length: 75 })
    OMSCHRIJVING: string;

    @Column({ type: 'tinyint', unsigned: true, nullable: true })
    SORTEER_VOLGORDE: number | null;

    @Column({ type: 'tinyint', unsigned: true, default: 0 })
    READ_ONLY: number;

    @Column({ type: 'decimal', precision: 6, scale: 2, nullable: true })
    BEDRAG: number | null;

    @Column({ type: 'decimal', precision: 6, scale: 2, nullable: true })
    EENHEDEN: number | null;

    @Column("boolean", { default: false, name: "VERWIJDERD" })
    VERWIJDERD: boolean;

    @UpdateDateColumn({ type: 'timestamp', default: () => 'CURRENT_TIMESTAMP', onUpdate: 'CURRENT_TIMESTAMP' })
    LAATSTE_AANPASSING: Date;
}
