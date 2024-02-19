
import { Controller } from '@nestjs/common';
import { ApiTags } from '@nestjs/swagger';

@Controller('Rooster')
@ApiTags('Rooster')
export class RoosterController {
  constructor() {
  }
}