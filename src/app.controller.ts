import { Controller, Get } from '@nestjs/common';
import { AppService } from './app.service';

@Controller('Types')
export class AppController {
  constructor(private readonly appService: AppService) {}

  @Get('CreateTable')
  getHello(): string {
    return this.appService.getHello();
  }
}
