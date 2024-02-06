import { Module } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { TypeOrmModule } from '@nestjs/typeorm';
import { TypeOrmConfigService } from './typeorm/typeorm.service';
import { TypesModule } from './modules/Types/types.module';
import { TypesGroepenModule } from './modules/TypesGroepen/types-groepen.module';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    TypeOrmModule.forRootAsync({ useClass: TypeOrmConfigService }),
    TypesModule,
    TypesGroepenModule
  ],
  controllers: [],
  providers: [TypeOrmConfigService],
})
export class AppModule {}
