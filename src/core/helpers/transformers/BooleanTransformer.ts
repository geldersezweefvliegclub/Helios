import {ValueTransformer} from "typeorm/decorator/options/ValueTransformer";

export const booleanTransformer: ValueTransformer = {
    to: (value: number) => value === 1,
    from: (value: boolean) => value === true
}