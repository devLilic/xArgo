import {
    forwardRef,
    useEffect,
    useImperativeHandle,
    useRef,
    type InputHTMLAttributes,
    type Ref,
} from 'react';

type TextInputProps = InputHTMLAttributes<HTMLInputElement> & {
    isFocused?: boolean;
};

type TextInputHandle = {
    focus: () => void;
};

function TextInput(
    { type = 'text', className = '', isFocused = false, ...props }: TextInputProps,
    ref: Ref<TextInputHandle>,
) {
    const localRef = useRef<HTMLInputElement | null>(null);

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, [isFocused]);

    return (
        <input
            {...props}
            type={type}
            className={
                'rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ' +
                className
            }
            ref={localRef}
        />
    );
}

export default forwardRef(TextInput);
