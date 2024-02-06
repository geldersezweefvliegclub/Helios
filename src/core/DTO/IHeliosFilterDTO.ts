import { FindManyOptions, FindOptionsOrder, ObjectLiteral } from 'typeorm';

export abstract class IHeliosFilterDTO<Entity extends ObjectLiteral> {
  abstract buildTypeORMFindManyObject(): FindManyOptions<Entity>

  abstract get defaultSortOrder(): FindOptionsOrder<Entity>;

  /**
   * Zet de sortering om naar een FindOptionsOrder object
   * Input: SORT=CLUBKIST DESC, VOLGORDE, REGISTRATIE
   * Output: { CLUBKIST: 'DESC', VOLGORDE: 'ASC', REGISTRATIE: 'ASC' }
   * @param commaSeparatedString
   * @private
   */
  protected bouwSorteringOp(commaSeparatedString: string): FindOptionsOrder<Entity> {
    const order: Record<string, string> = {};

    const sortFields = commaSeparatedString.split(',');

    for (const sortField of sortFields) {
      const parts = sortField.trim().split(' ');
      const field = parts[0];
      // Pak de de waarde van de sortering, als die er niet is, dan default naar ASC
      order[field as keyof typeof order] = parts.length > 1 ? parts[1] : 'ASC';
    }

    return order as FindOptionsOrder<Entity>;
  }
}
