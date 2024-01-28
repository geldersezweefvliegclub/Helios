import {ApiProperty} from "@nestjs/swagger";
import {IsInt, IsNotEmpty} from "class-validator";

export class RestoreObjectDto {
    @ApiProperty()
    @IsNotEmpty()
    @IsInt()
    readonly ID: number;
}