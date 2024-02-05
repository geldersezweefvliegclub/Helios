import { IsNumber, IsOptional, IsString } from 'class-validator';
import { Transform } from 'class-transformer';

/**
 * Een generieke DTO gebruikt door `GetObjects` endpoints zodat NestJS de query parameters kan valideren en parsen.
 * Deze properties kunnen voor elk willekeurig object die `GetObjects` implementeert worden gebruikt.
 * Voeg extra properties toe aan deze DTO door een subclass te maken van deze DTO en die te gebruiken in de `GetObjects` endpoint.
 */
export class GetObjectsFilterDTO {
  @IsOptional()
  @IsNumber()
  @Transform((params) => params.value == null ? null : parseInt(params.value))
  MAX?: number;

  @IsOptional()
  @IsNumber()
  START?: number;

  @IsOptional()
  @IsString()
  VELDEN?: string;

  @IsOptional()
  @IsString()
  SORT?: string;
}
