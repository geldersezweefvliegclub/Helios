import { Module } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { TypeOrmModule } from '@nestjs/typeorm';
import { TypeOrmConfigService } from './typeorm/typeorm.service';
import { TypesModule } from './modules/Types/types.module';
import { TypesGroepenModule } from './modules/TypesGroepen/types-groepen.module';
import { VliegtuigenModule } from './modules/Vliegtuigen/vliegtuigen.module';
import { LedenModule } from './modules/Leden/Leden.module';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    TypeOrmModule.forRootAsync({ useClass: TypeOrmConfigService}),
    TypesModule,
    TypesGroepenModule,
    VliegtuigenModule,
    LedenModule,
  ],
  controllers: [],
  providers: [TypeOrmConfigService],
})
export class AppModule {}
