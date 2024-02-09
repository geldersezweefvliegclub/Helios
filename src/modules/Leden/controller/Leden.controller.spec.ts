import { Test, TestingModule } from '@nestjs/testing';
import { LedenController } from './Leden.controller';
import { LedenService } from '../service/Leden.service';

describe('LedenController', () => {
  let controller: LedenController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [LedenController],
      providers: [{ provide: LedenService, useValue: jest.fn() }]
    }).compile();

    controller = module.get<LedenController>(LedenController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
