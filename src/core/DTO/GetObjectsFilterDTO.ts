import { IsNumber, IsOptional, IsString } from 'class-validator';

/**
 * Een generieke DTO gebruikt door `GetObjects` endpoints zodat NestJS de query parameters kan valideren en parsen.
 * Deze properties kunnen voor elk willekeurig object die `GetObjects` implementeert worden gebruikt.
 * Voeg extra properties toe aan deze DTO door een subclass te maken van deze DTO en die te gebruiken in de `GetObjects` endpoint.
 */
export class GetObjectsFilterDTO {
  @IsOptional()
  @IsNumber()
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
