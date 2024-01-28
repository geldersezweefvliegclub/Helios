import {ApiProperty} from "@nestjs/swagger";
import {IsInt, IsNotEmpty} from "class-validator";

export class GetObjectDto {
    @ApiProperty()
    @IsNotEmpty()
    @IsInt()
    readonly ID: number;
}