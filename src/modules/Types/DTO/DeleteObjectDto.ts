import {ApiProperty} from "@nestjs/swagger";
import {IsBoolean, IsInt, IsNotEmpty, IsOptional} from "class-validator";

export class DeleteObjectDto {
    @ApiProperty()
    @IsNotEmpty()
    @IsInt()
    readonly ID: number;

    @ApiProperty()
    @IsOptional()
    @IsBoolean()
    readonly VERIFICATIE: boolean;
}