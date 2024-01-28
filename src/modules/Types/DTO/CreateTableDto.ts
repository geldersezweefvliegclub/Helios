import {ApiProperty} from "@nestjs/swagger";
import {IsBoolean, IsNotEmpty} from "class-validator";

export class CreateTableDto {
    @ApiProperty()
    @IsNotEmpty()
    @IsBoolean()
    readonly FILLDATA: boolean;
}