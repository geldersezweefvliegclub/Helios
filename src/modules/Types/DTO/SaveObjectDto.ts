import {ApiProperty} from "@nestjs/swagger";
import {IsBoolean, IsInt, IsNotEmpty, IsOptional, IsString} from "class-validator";

export class SaveObjectDto {
    @ApiProperty()
    @IsOptional()
    @IsInt()
    readonly ID: number;

    @ApiProperty()
    @IsNotEmpty()
    @IsInt()
    readonly GROEP: number;

    @ApiProperty()
    @IsNotEmpty()
    @IsString()
    readonly CODE: string;

    @ApiProperty()
    @IsNotEmpty()
    @IsString()
    readonly EXT_REF: string;

    @ApiProperty()
    @IsNotEmpty()
    @IsString()
    readonly OMSCHRIJVING: string;

    @ApiProperty()
    @IsNotEmpty()
    @IsInt()
    readonly SORTEER_VOLGORDE: number;

    @ApiProperty()
    @IsNotEmpty()
    @IsBoolean()
    readonly READ_ONLY: boolean;

    @ApiProperty()
    @IsOptional()
    @IsInt()
    readonly BEDRAG: number;

    @ApiProperty()
    @IsOptional()
    @IsInt()
    readonly EENHEDEN: number;
}